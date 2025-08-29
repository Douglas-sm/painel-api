<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedTenantUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all tenant databases with users defined in TenantUserSeeder';

    /**
     * Process a user - create if not exists or update if exists
     */
    protected function processUser(Tenant $tenant, string $name, string $email, string $password): void
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->info("  - Updating existing user: {$name} ({$email})");
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'tenant_id' => $tenant->id,
            ]);
        } else {
            $this->info("  - Creating new user: {$name} ({$email})");
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'tenant_id' => $tenant->id,
            ]);
        }
    }

    public function handle()
    {
        // Get all tenants from the central database
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->info('No tenants found in the database.');
            return;
        }

        $this->info('Starting user seeding for ' . $tenants->count() . ' tenants...');

        foreach ($tenants as $tenant) {
            $this->info("Seeding users for tenant: {$tenant->name} (Database: {$tenant->database})");

            // Configure connection for the tenant's database
            Config::set('database.connections.tenant', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $tenant->database,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', 'root'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]);

            // Reset and reconfigure the tenant connection to ensure a clean state
            DB::purge('tenant');
            Config::set('database.default', 'tenant');

            try {
                // Store current tenant for seeders to use
                app()->instance('tenant', $tenant);

                // Create a custom implementation to handle existing users
                $this->info("Seeding/updating users for tenant: {$tenant->name}");

                // Process users based on tenant ID
                switch($tenant->id) {
                    case 1: // Alfa
                        $this->processUser($tenant, 'João', 'joao@alfa.com', 'Do741852!');
                        $this->processUser($tenant, 'Maria', 'maria@alfa.com', 'Do741852!');
                        break;

                    case 2: // Beta
                        $this->processUser($tenant, 'Pedro', 'pedro@beta.com', 'Do741852!');
                        break;

                    case 3: // Celta
                        $this->processUser($tenant, 'Ana', 'ana@celta.com', 'Do741852!');
                        break;

                    default:
                        $this->info("No specific users defined for tenant ID: {$tenant->id}");
                }

                $this->info("User seeding/updating completed for tenant: {$tenant->name}");
            } catch (\Exception $e) {
                $this->error("Error processing tenant {$tenant->name}: " . $e->getMessage());
            }
        }

        // Reset back to the central database connection
        Config::set('database.default', 'mysql');

        $this->info('All tenant databases have been seeded with users.');
    }
}
