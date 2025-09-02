<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default theme colors
        DB::table('themes')->insert([
            'primary' => '#FF5733',
            'secondary' => '#33FF57',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the default theme
        DB::table('themes')->where([
            'primary' => '#FF5733',
            'secondary' => '#33FF57',
        ])->delete();
    }
};
