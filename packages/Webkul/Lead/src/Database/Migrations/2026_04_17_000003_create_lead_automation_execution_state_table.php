<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_automation_execution_state')) {
            return;
        }

        Schema::create('lead_automation_execution_state', function ($table) {
            $table->id();
            $table->string('fingerprint', 64)->unique();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('trigger_type', 50);
            $table->string('trigger_name', 100);
            $table->string('event', 20);
            $table->string('action_hash', 64)->nullable();
            $table->unsignedTinyInteger('hit_count')->default(1);
            $table->json('execution_fingerprint')->nullable();
            $table->unsignedInteger('execution_count')->default(0);
            $table->timestamp('first_executed_at')->useCurrent();
            $table->timestamp('last_executed_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->json('guard_results')->nullable();
            $table->string('blocked_reason', 255)->nullable();
            $table->index(['lead_id', 'trigger_type']);
            $table->index(['fingerprint', 'last_executed_at']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_automation_execution_state');
    }
};
