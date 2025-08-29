<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Make sure we have users
        $users = User::all();

        if ($users->isEmpty()) {
            // Create a user if none exists
            $users = User::factory(3)->create();
        }

        // Create 10 posts
        for ($i = 1; $i <= 10; $i++) {
            $title = "Sample Post $i";

            Post::create([
                'title' => $title,
                'content' => "This is the content for sample post $i. It contains detailed information about the topic.",
                'user_id' => $users->random()->id,
                'slug' => Str::slug($title),
                'excerpt' => "Short excerpt for post $i",
                'status' => $i % 3 == 0 ? 'draft' : 'published', // Every 3rd post is a draft
            ]);
        }
    }
}
