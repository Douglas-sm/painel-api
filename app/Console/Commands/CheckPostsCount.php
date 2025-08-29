<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class CheckPostsCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the number of posts in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Post::count();
        $this->info("Number of posts in the database: {$count}");

        if ($count == 10) {
            $this->info("✓ Successfully created 10 posts!");

            // Show a sample of posts
            $posts = Post::take(3)->get(['id', 'title', 'user_id', 'status']);
            $this->table(['ID', 'Title', 'User ID', 'Status'], $posts->toArray());
        } else {
            $this->error("✗ Expected 10 posts, but found {$count}");
        }

        return 0;
    }
}
