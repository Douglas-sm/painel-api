<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = app('tenant');

        if (!$tenant) {
            $this->command->error('No tenant context found for seeding users');
            return;
        }

        // We'll skip truncating since we're working with fresh databases
        // Also, truncate wouldn't work with foreign key constraints
        // User::truncate();

        $this->command->info("Seeding users for tenant: {$tenant->name}");

        // Seed specific users based on tenant
        switch($tenant->id) {
            case 1: // Alfa
                User::create([
                    'id' => 1,
                    'name' => 'João',
                    'email' => 'joao@alfa.com',
                    'password' => Hash::make('Do741852!'),
                    'tenant_id' => $tenant->id,
                ]);

                User::create([
                    'id' => 2,
                    'name' => 'Maria',
                    'email' => 'maria@alfa.com',
                    'password' => Hash::make('Do741852!'),
                    'tenant_id' => $tenant->id,
                ]);

                break;

            case 2: // Beta
                User::create([
                    'id' => 1,
                    'name' => 'Pedro',
                    'email' => 'pedro@beta.com',
                    'password' => Hash::make('Do741852!'),
                    'tenant_id' => $tenant->id,
                ]);
                break;

            case 3: // Celta
                User::create([
                    'id' => 1,
                    'name' => 'Ana',
                    'email' => 'ana@celta.com',
                    'password' => Hash::make('Do741852!'),
                    'tenant_id' => $tenant->id,
                ]);
                break;

            default:
                $this->command->info("No specific users defined for tenant ID: {$tenant->id}");
        }

        $this->command->info("Finished seeding users for tenant: {$tenant->name}");
    }
}
