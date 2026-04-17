<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAutomationExecutionState;

/**
 * Automation Guard - Prevents duplicate, loops, and floods
 *
 * Three-layer protection:
 * 1. Deduplication - prevents same lead+event firing twice in short window
 * 2. Loop prevention - blocks circular automation chains
 * 3. Flood protection - rate-limits executions per lead
 */
class AutomationGuard
{
    protected const DEDUP_TTL = 60;

    protected const LOOP_TTL = 300;

    protected const FLOOD_THRESHOLD = 5;

    protected const FLOOD_WINDOW = 300;

    /**
     * Check if execution should proceed
     */
    public function canExecute(Lead $lead, string $event, array $actionHash): array
    {
        $state = $this->getOrCreateState($lead->id);

        // Layer 1: Deduplication
        $dedupKey = $this->getDeduplicationKey($lead->id, $event);
        if ($state->isDuplicateRecently($dedupKey)) {
            return [false, 'duplicate_suppressed', 'Deduplication: same lead+event recently executed'];
        }

        // Layer 2: Loop Prevention
        $loopKey = $this->getLoopKey($actionHash);
        if ($state->hasLoopDetected($loopKey, self::LOOP_TTL / 60)) {
            return [false, 'loop_suppressed', 'Loop prevention: circular automation detected'];
        }

        // Layer 3: Flood Protection
        if ($state->exceedsFloodThreshold(self::FLOOD_THRESHOLD, self::FLOOD_WINDOW)) {
            return [false, 'flood_suppressed', 'Flood protection: too many executions'];
        }

        return [true, null, null];
    }

    /**
     * Record successful execution
     */
    public function recordExecution(Lead $lead, string $event, array $actionHash): void
    {
        $state = $this->getOrCreateState($lead->id);

        $state->recordDeduplication($this->getDeduplicationKey($lead->id, $event));
        $state->recordLoop($this->getLoopKey($actionHash));
        $state->recordFlood();
    }

    /**
     * Record failed execution (still counts for flood protection)
     */
    public function recordFailure(Lead $lead, string $event, array $actionHash, string $error): void
    {
        $state = $this->getOrCreateState($lead->id);
        $state->recordFlood();
    }

    protected function getDeduplicationKey(int $leadId, string $event): string
    {
        return "lead:{$leadId}:event:{$event}";
    }

    protected function getLoopKey(array $actionHash): string
    {
        return 'action:'.md5(json_encode($actionHash));
    }

    protected function getOrCreateState(int $leadId): LeadAutomationExecutionState
    {
        $state = LeadAutomationExecutionState::firstOrCreate(
            ['lead_id' => $leadId],
            [
                'execution_count' => 0,
                'execution_fingerprint' => json_encode([]),
                'last_executed_at' => now(),
            ]
        );

        return $state;
    }
}
