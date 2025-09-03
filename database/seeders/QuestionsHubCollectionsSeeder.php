<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class QuestionsHubCollectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 5 subject categories in the questions_hub MySQL database
     */
    public function run(): void
    {
        // Configure a connection to questions_hub database
        Config::set('database.connections.questions_hub', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'questions_hub',
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Check if categories table exists, create it if it doesn't
        if (!Schema::connection('questions_hub')->hasTable('subject_categories')) {
            Schema::connection('questions_hub')->create('subject_categories', function ($table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
            $this->command->info("Table 'subject_categories' created successfully in questions_hub database.");
        }

        // Create 5 subject categories
        $subjects = [
            'Matemática',
            'Português',
            'Física',
            'Ciências',
            'História'
        ];

        foreach ($subjects as $subject) {
            // Check if subject already exists
            $exists = DB::connection('questions_hub')
                ->table('subject_categories')
                ->where('name', $subject)
                ->exists();

            // Insert subject if it doesn't exist
            if (!$exists) {
                DB::connection('questions_hub')
                    ->table('subject_categories')
                    ->insert([
                        'name' => $subject,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                $this->command->info("Subject '{$subject}' created successfully in questions_hub database.");
            } else {
                $this->command->info("Subject '{$subject}' already exists in questions_hub database.");
            }
        }
    }
}
