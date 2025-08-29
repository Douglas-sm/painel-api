<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'domain', 'database'];

    // Relações com usuários ou outros modelos específicos do tenant
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
