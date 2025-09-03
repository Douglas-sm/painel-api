<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Faker\Factory as Faker;

class QuestionsHubQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 50 questions with 4 choices each in the questions_hub MySQL database
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

        // Initialize faker
        $faker = Faker::create();

        // Check if questions table exists, create it if it doesn't
        if (!Schema::connection('questions_hub')->hasTable('questions')) {
            Schema::connection('questions_hub')->create('questions', function ($table) {
                $table->id();
                $table->unsignedBigInteger('collection_id');
                $table->string('text');
                $table->timestamps();
            });
            $this->command->info("Table 'questions' created successfully in questions_hub database.");
        }

        // Check if choices table exists, create it if it doesn't
        if (!Schema::connection('questions_hub')->hasTable('choices')) {
            Schema::connection('questions_hub')->create('choices', function ($table) {
                $table->id();
                $table->unsignedBigInteger('question_id');
                $table->string('text');
                $table->boolean('is_correct');
                $table->timestamps();

                // Add foreign key constraint
                $table->foreign('question_id')
                    ->references('id')
                    ->on('questions')
                    ->onDelete('cascade');
            });
            $this->command->info("Table 'choices' created successfully in questions_hub database.");
        }

        // Get collection ids from the subject_categories table
        $collectionIds = DB::connection('questions_hub')
            ->table('subject_categories')
            ->pluck('id')
            ->toArray();

        // If no collections found, use default range
        if (empty($collectionIds)) {
            $this->command->warn("No collections found in database. Using default range 1-5.");
            $collectionIds = range(1, 5);
        }

        // Create 50 questions
        $this->command->info("Creating 50 questions with 4 choices each...");

        for ($i = 1; $i <= 50; $i++) {
            // Insert a question with random collection_id
            $randomCollectionId = $collectionIds[array_rand($collectionIds)];

            $questionId = DB::connection('questions_hub')
                ->table('questions')
                ->insertGetId([
                    'collection_id' => $randomCollectionId,
                    'content' => $faker->sentence(rand(10, 15)) . '?',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            // Create 4 choices for this question
            $correctChoiceIndex = rand(0, 3); // Randomly determine which choice is correct

            for ($j = 0; $j < 4; $j++) {
                DB::connection('questions_hub')
                    ->table('choices')
                    ->insert([
                        'question_id' => $questionId,
                        'content' => $faker->sentence(rand(3, 8)),
                        'is_correct' => ($j === $correctChoiceIndex),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            if ($i % 10 === 0) {
                $this->command->info("Created {$i} questions so far...");
            }
        }

        $this->command->info("Successfully created 50 questions with 4 choices each in the questions_hub database.");
    }
}
