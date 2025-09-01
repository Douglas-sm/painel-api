<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentralDatabaseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding user for central database (painel_admin)');

        // Create the specified user
        User::create([
            'name' => 'Douglas', // Using a name based on the email
            'email' => 'douglas.stmr@gmail.com',
            'password' => Hash::make('Do741852!'),
            // No tenant_id as this is for the central database
        ]);

        $this->command->info('Finished seeding user for central database');
    }
}
