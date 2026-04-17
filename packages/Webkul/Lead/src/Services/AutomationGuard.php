<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Log;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAutomationExecutionState;

/**
 * Automation Guard — Prevents duplicates, loops, and floods.
 *
 * Three-layer protection:
 *   1. Deduplication  — prevents same lead+event firing twice in short window
 *   2. Loop prevention — blocks circular automation chains
 *   3. Flood protection — rate-limits executions per lead
 *
 * Public API used by LeadTemperatureClassifier:
 *   - check(Lead, string)  → returns array of failure reasons (empty = OK)
 *   - record(Lead, string) → records successful execution
 *
 * Public API used by AutomationGuard internally (and available for rules):
 *   - canExecute(Lead, string, array) → [bool, reason, message]
 *   - recordExecution(Lead, string, array)
 *   - recordFailure(Lead, string, array, string)
 */
class AutomationGuard
{
    protected const DEDUP_TTL       = 60;   // seconds
    protected const LOOP_TTL        = 300;  // seconds
    protected const FLOOD_THRESHOLD = 5;
    protected const FLOOD_WINDOW    = 300;  // seconds

    // =====================================================================
    // Simple API — used by LeadTemperatureClassifier
    // =====================================================================

    /**
     * Check if execution should proceed.
     * Returns an array of failure reasons; empty array means "go ahead".
     *
     * @param  Lead    $lead
     * @param  string  $event
     * @return array<string>
     */
    public function check(Lead $lead, string $event): array
    {
        $failures   = [];
        $actionHash = [];

        [$allowed, $reason] = $this->canExecute($lead, $event, $actionHash);

        if (! $allowed) {
            $failures[] = $reason;
        }

        return $failures;
    }

    /**
     * Record a successful execution (simple API for LeadTemperatureClassifier).
     *
     * @param  Lead    $lead
     * @param  string  $event
     * @return void
     */
    public function record(Lead $lead, string $event): void
    {
        $this->recordExecution($lead, $event, []);
    }

    // =====================================================================
    // Full API — used by LeadAutomationRuleEngine and internally
    // =====================================================================

    /**
     * Check if execution should proceed (full API with action hash).
     *
     * @param  Lead    $lead
     * @param  string  $event
     * @param  array   $actionHash
     * @return array{bool, string|null, string|null}
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
     * Record successful execution (full API).
     *
     * @param  Lead    $lead
     * @param  string  $event
     * @param  array   $actionHash
     * @return void
     */
    public function recordExecution(Lead $lead, string $event, array $actionHash): void
    {
        $state = $this->getOrCreateState($lead->id);

        $state->recordDeduplication($this->getDeduplicationKey($lead->id, $event), self::DEDUP_TTL);
        $state->recordLoop($this->getLoopKey($actionHash));
        $state->recordFlood();
    }

    /**
     * Record failed execution (still counts toward flood protection).
     *
     * @param  Lead    $lead
     * @param  string  $event
     * @param  array   $actionHash
     * @param  string  $error
     * @return void
     */
    public function recordFailure(Lead $lead, string $event, array $actionHash, string $error): void
    {
        $state = $this->getOrCreateState($lead->id);
        $state->recordFlood();

        Log::warning("AutomationGuard: execution failed for lead {$lead->id}", [
            'event' => $event,
            'error' => $error,
        ]);
    }

    // =====================================================================
    // Internals
    // =====================================================================

    protected function getDeduplicationKey(int $leadId, string $event): string
    {
        return "lead:{$leadId}:event:{$event}";
    }

    protected function getLoopKey(array $actionHash): string
    {
        return 'action:' . md5(json_encode($actionHash));
    }

    protected function getOrCreateState(int $leadId): LeadAutomationExecutionState
    {
        return LeadAutomationExecutionState::firstOrCreate(
            ['lead_id' => $leadId],
            [
                'execution_count'       => 0,
                'execution_fingerprint' => [],
                'last_executed_at'      => now(),
            ]
        );
    }
}
