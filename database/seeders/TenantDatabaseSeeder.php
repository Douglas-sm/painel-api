<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     */
    public function run(): void
    {
        // Call tenant-specific seeders
        $this->call([
            TenantUserSeeder::class,
            TenantProductSeeder::class,
            TenantThemeSeeder::class,
        ]);
    }
}
