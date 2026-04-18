<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_automation_logs')) {
            return;
        }

        Schema::create('lead_automation_logs', function ($table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('trigger_type');
            $table->string('trigger_name');
            $table->string('event');
            $table->json('context');
            $table->json('actions_executed');
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'trigger_type']);
            $table->index(['trigger_type', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_automation_logs');
    }
};
