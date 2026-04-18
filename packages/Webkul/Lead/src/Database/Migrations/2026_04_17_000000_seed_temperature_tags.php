<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip entirely if tags table doesn't exist
        if (! Schema::hasTable('tags')) {
            return;
        }

        $tags = [
            ['name' => 'hot',  'color' => '#EF4444'],
            ['name' => 'warm', 'color' => '#F59E0B'],
            ['name' => 'cold', 'color' => '#3B82F6'],
        ];

        foreach ($tags as $tag) {
            try {
                $exists = DB::table('tags')->where('name', $tag['name'])->exists();
                if (! $exists) {
                    DB::statement(
                        'INSERT IGNORE INTO tags (name, color, created_at, updated_at) VALUES (?, ?, NOW(), NOW())',
                        [$tag['name'], $tag['color']]
                    );
                }
            } catch (Throwable $e) {
                // Silently skip — tags are non-critical seed data
                continue;
            }
        }
    }

    public function down(): void
    {
        DB::table('tags')->whereIn('name', ['hot', 'warm', 'cold'])->delete();
    }
};
