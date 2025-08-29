<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class TenantThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = app('tenant');

        if (!$tenant) {
            $this->command->error('No tenant context found for seeding themes');
            return;
        }

        $this->command->info("Seeding themes for tenant: {$tenant->name}");

        // Seed specific theme colors based on tenant
        switch($tenant->id) {
            case 1: // Alfa
                Theme::create([
                    'primary' => '#FF5733',
                    'secondary' => '#33FF57',
                ]);
                break;

            case 2: // Beta
                Theme::create([
                    'primary' => '#3357FF',
                    'secondary' => '#FF33A8',
                ]);
                break;

            case 3: // Celta
                Theme::create([
                    'primary' => '#A833FF',
                    'secondary' => '#FFD133',
                ]);
                break;

            default:
                // Default theme for any other tenant
                Theme::create([
                    'primary' => '#007BFF',
                    'secondary' => '#6C757D',
                ]);
        }

        $this->command->info("Finished seeding themes for tenant: {$tenant->name}");
    }
}
