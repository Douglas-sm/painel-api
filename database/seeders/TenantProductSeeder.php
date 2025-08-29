<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

class TenantProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = app('tenant');

        if (!$tenant) {
            $this->command->error('No tenant context found for seeding products');
            return;
        }

        // We'll skip truncating since we're working with fresh databases
        // Product::truncate();

        $this->command->info("Seeding products for tenant: {$tenant->name}");

        // Seed specific products based on tenant
        switch($tenant->id) {
            case 1: // Alfa
                Product::create([
                    'id' => 1,
                    'name' => 'Curso Matemática',
                    'description' => 'Descrição...',
                    'price' => 150.00,
                ]);

                Product::create([
                    'id' => 2,
                    'name' => 'Curso Português',
                    'description' => 'Descrição...',
                    'price' => 180.00,
                ]);
                break;

            case 2: // Beta
                Product::create([
                    'id' => 1,
                    'name' => 'Curso Inglês',
                    'description' => 'Descrição...',
                    'price' => 200.00,
                ]);
                break;

            case 3: // Celta
                Product::create([
                    'id' => 1,
                    'name' => 'Curso História',
                    'description' => 'Descrição...',
                    'price' => 120.00,
                ]);
                break;

            default:
                $this->command->info("No specific products defined for tenant ID: {$tenant->id}");
        }

        $this->command->info("Finished seeding products for tenant: {$tenant->name}");
    }
}
