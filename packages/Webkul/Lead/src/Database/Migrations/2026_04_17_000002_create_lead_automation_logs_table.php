<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_automation_logs', function ($table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // What triggered this
            $table->string('trigger_type'); // 'temperature_scoring' | 'automation_rule'
            $table->string('trigger_name'); // 'LeadTemperatureScoring' or rule name
            
            // Execution context
            $table->string('event'); // 'created' | 'updated'
            $table->json('context'); // {score, matched_conditions, threshold, dirty_fields}
            
            // What actions were executed
            $table->json('actions_executed'); // [{action, params, result, error}]
            
            // Status tracking
            $table->string('status'); // 'success' | 'partial' | 'failed'
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // extra info
            
            $table->timestamps();
            
            // Indexes for filtering
            $table->index(['lead_id', 'trigger_type']);
            $table->index(['trigger_type', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_automation_logs');
    }
};