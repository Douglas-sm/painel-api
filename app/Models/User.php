<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
    ];

    /**
     * Determine if the user is a super admin.
     * A super admin is a user from the central database (tenant_id is null)
     * and accessing from a central domain (localhost or central.localhost).
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        // Check if user is from central database (tenant_id is null)
        if ($this->tenant_id !== null) {
            return false;
        }

        // Check if accessing from a central domain
        $host = request()->getHost();
        $centralDomains = array_filter(array_map('trim', explode(',', env('CENTRAL_DOMAINS', 'localhost'))));

        return in_array($host, $centralDomains, true);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the tenant that the user belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
