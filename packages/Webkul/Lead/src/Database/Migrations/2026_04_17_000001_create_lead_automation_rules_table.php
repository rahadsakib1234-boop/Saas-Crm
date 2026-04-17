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
        Schema::create('lead_automation_rules', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // lower = higher priority
            $table->string('entity_type')->default('leads');
            
            // Trigger configuration (JSON): {"event": "created|updated", "conditions": [...], "condition_logic": "and|or"}
            $table->json('trigger_config')->nullable();
            
            // Action configuration (JSON): [{"action": "add_tag", "params": {"tag": "hot"}}]
            $table->json('actions');
            
            // Rule matching logic
            $table->string('condition_logic')->default('and'); // and/or
            
            $table->timestamps();
        });

        Schema::create('lead_automation_rule_conditions', function ($table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('lead_automation_rules')->onDelete('cascade');
            $table->string('field');           // e.g., "description", "title"
            $table->string('operator');        // e.g., "contains", "equals", "starts_with"
            $table->string('value');           // the condition value to match
            $table->integer('condition_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_automation_rule_conditions');
        Schema::dropIfExists('lead_automation_rules');
    }
};