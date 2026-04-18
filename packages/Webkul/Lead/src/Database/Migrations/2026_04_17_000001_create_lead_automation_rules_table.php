<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_automation_rules')) {
            Schema::create('lead_automation_rules', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('priority')->default(0);
                $table->string('entity_type')->default('leads');
                $table->json('trigger_config')->nullable();
                $table->json('actions');
                $table->string('condition_logic')->default('and');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('lead_automation_rule_conditions')) {
            Schema::create('lead_automation_rule_conditions', function ($table) {
                $table->id();
                $table->foreignId('rule_id')->constrained('lead_automation_rules')->onDelete('cascade');
                $table->string('field');
                $table->string('operator');
                $table->string('value');
                $table->integer('condition_order')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_automation_rule_conditions');
        Schema::dropIfExists('lead_automation_rules');
    }
};
