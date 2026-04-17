<?php

namespace Webkul\Lead\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Webkul\Tag\Models\Tag;
use Webkul\User\Models\UserProxy;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed default temperature tags if they don't exist
        $userId = UserProxy::first()?->id;

        $tags = [
            ['name' => 'hot', 'color' => '#EF4444', 'user_id' => $userId],
            ['name' => 'warm', 'color' => '#F59E0B', 'user_id' => $userId],
            ['name' => 'cold', 'color' => '#3B82F6', 'user_id' => $userId],
        ];

        foreach ($tags as $tagData) {
            Tag::firstOrCreate(
                ['name' => $tagData['name']],
                $tagData
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Tag::whereIn('name', ['hot', 'warm', 'cold'])->delete();
    }
};
