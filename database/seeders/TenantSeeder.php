<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tenants to avoid duplicate keys
        Tenant::truncate();

        // Create tenants with specific IDs as per requirements
        Tenant::create([
            'id' => 1,
            'name' => 'Alfa',
            'domain' => 'alfa',
            'database' => 'painel_alfa',
        ]);

        Tenant::create([
            'id' => 2,
            'name' => 'Beta',
            'domain' => 'beta',
            'database' => 'painel_beta',
        ]);

        Tenant::create([
            'id' => 3,
            'name' => 'Celta',
            'domain' => 'celta',
            'database' => 'painel_celta',
        ]);
    }
}
