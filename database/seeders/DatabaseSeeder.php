<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // This seeder is for the central database only
        // It will create the tenant records
        $this->call(TenantSeeder::class);

        // Add user to central database
        $this->call(CentralDatabaseUserSeeder::class);

        // Create subject categories in questions_hub MySQL database
        $this->call(QuestionsHubCollectionsSeeder::class);

        // Create questions with choices in questions_hub MySQL database
        $this->call(QuestionsHubQuestionsSeeder::class);
    }
}
