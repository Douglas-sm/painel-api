<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the current tenant domain from the database connection
        $tenantDomain = null;
        $databaseName = DB::connection()->getDatabaseName();

        // Extract domain from database name (assuming format: painel_domain)
        if (strpos($databaseName, 'painel_') === 0) {
            $tenantDomain = substr($databaseName, 7);
        }

        if (!$tenantDomain) {
            // Fallback to a default if the domain can't be extracted
            $tenantDomain = 'default';
        }

        // Create admin user with the tenant domain in the email
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => "admin@{$tenantDomain}.com",
            'password' => Hash::make('Do741852!'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete admin user
        DB::table('users')->where('name', 'Admin')->delete();
    }
};
