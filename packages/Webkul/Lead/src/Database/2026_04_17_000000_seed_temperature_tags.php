<?php

namespace Webkul\Lead\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Uses raw DB insert instead of the Tag model to avoid
     * user_id NOT NULL constraint on a fresh database where
     * no users exist yet at migration time.
     */
    public function up(): void
    {
        $tags = [
            ['name' => 'hot',  'color' => '#EF4444'],
            ['name' => 'warm', 'color' => '#F59E0B'],
            ['name' => 'cold', 'color' => '#3B82F6'],
        ];

        foreach ($tags as $tag) {
            $exists = DB::table('tags')->where('name', $tag['name'])->exists();

            if (! $exists) {
                DB::table('tags')->insert(array_merge($tag, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tags')->whereIn('name', ['hot', 'warm', 'cold'])->delete();
    }
};
