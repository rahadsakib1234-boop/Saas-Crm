<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores automation execution fingerprints for:
     *   1. Preventing duplicate executions across servers
     *   2. Loop detection with hit counters
     *   3. Flood protection with total counts per lead
     *
     * Why DB instead of cache?
     *   - Cache resets on restart → false triggers
     *   - Multi-server = inconsistent state with cache
     *   - DB = single source of truth across all instances
     */
    public function up(): void
    {
        Schema::create('lead_automation_execution_state', function ($table) {
            $table->id();

            // Unique fingerprint for this execution
            $table->string('fingerprint', 64)->unique();

            // Context
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set-null');

            // What ran
            $table->string('trigger_type', 50);  // 'temperature_scoring' | 'automation_rule'
            $table->string('trigger_name', 100);
            $table->string('event', 20);          // 'created' | 'updated'
            $table->string('action_hash', 64)->nullable();  // hash of action for loop detection

            // State tracking
            $table->unsignedTinyInteger('hit_count')->default(1);
            $table->timestamp('first_executed_at')->useCurrent();
            $table->timestamp('last_executed_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();  // TTL for auto-cleanup

            // Guard results
            $table->json('guard_results')->nullable();
            $table->string('blocked_reason', 255)->nullable();

            // Indexes for performance
            $table->index(['lead_id', 'trigger_type']);
            $table->index(['fingerprint', 'last_executed_at']);
            $table->index(['expires_at']);  // for cleanup job
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_automation_execution_state');
    }
};
