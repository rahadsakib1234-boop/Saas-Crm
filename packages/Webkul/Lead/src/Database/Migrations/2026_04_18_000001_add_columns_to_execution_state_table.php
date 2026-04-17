<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add execution_fingerprint and execution_count columns that
 * LeadAutomationExecutionState needs for guard methods.
 *
 * These were referenced in AutomationGuard::getOrCreateState() firstOrCreate()
 * but were missing from the original migration schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_automation_execution_state', function (Blueprint $table) {
            // Stores dedup + loop fingerprint timestamps as JSON map
            if (! Schema::hasColumn('lead_automation_execution_state', 'execution_fingerprint')) {
                $table->json('execution_fingerprint')->nullable()->after('action_hash');
            }

            // Total executions for flood detection
            if (! Schema::hasColumn('lead_automation_execution_state', 'execution_count')) {
                $table->unsignedInteger('execution_count')->default(0)->after('execution_fingerprint');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_automation_execution_state', function (Blueprint $table) {
            $table->dropColumn(['execution_fingerprint', 'execution_count']);
        });
    }
};
