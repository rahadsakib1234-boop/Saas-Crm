<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\LeadAutomationExecutionState as LeadAutomationExecutionStateContract;

/**
 * Automation Execution State
 *
 * Stores fingerprints to prevent duplicate/loop/flood executions.
 * Replaces cache-based guards for multi-server scalability.
 */
class LeadAutomationExecutionState extends Model implements LeadAutomationExecutionStateContract
{
    protected $table = 'lead_automation_execution_state';

    protected $fillable = [
        'fingerprint',
        'lead_id',
        'user_id',
        'trigger_type',
        'trigger_name',
        'event',
        'action_hash',
        'hit_count',
        'execution_count',
        'execution_fingerprint',
        'first_executed_at',
        'last_executed_at',
        'expires_at',
        'guard_results',
        'blocked_reason',
    ];

    protected $casts = [
        'hit_count'             => 'integer',
        'execution_count'       => 'integer',
        'execution_fingerprint' => 'array',
        'guard_results'         => 'array',
        'first_executed_at'     => 'datetime',
        'last_executed_at'      => 'datetime',
        'expires_at'            => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    // =====================================================================
    // Guard helpers — called by AutomationGuard
    // =====================================================================

    /**
     * Check if this lead+event key was recently executed (deduplication).
     *
     * @param  string  $dedupKey
     * @return bool
     */
    public function isDuplicateRecently(string $dedupKey): bool
    {
        $fingerprints = $this->execution_fingerprint ?? [];

        if (! isset($fingerprints[$dedupKey])) {
            return false;
        }

        $executedAt = $fingerprints[$dedupKey]['executed_at'] ?? 0;
        $ttl        = $fingerprints[$dedupKey]['ttl'] ?? 60;

        return (time() - $executedAt) < $ttl;
    }

    /**
     * Check if a loop is detected for a given action hash.
     *
     * @param  string  $loopKey
     * @param  int     $ttlMinutes
     * @return bool
     */
    public function hasLoopDetected(string $loopKey, int $ttlMinutes = 5): bool
    {
        $fingerprints = $this->execution_fingerprint ?? [];

        if (! isset($fingerprints[$loopKey])) {
            return false;
        }

        $executedAt = $fingerprints[$loopKey]['executed_at'] ?? 0;
        $ttlSeconds = $ttlMinutes * 60;

        return (time() - $executedAt) < $ttlSeconds;
    }

    /**
     * Check if execution count exceeds flood threshold within time window.
     *
     * @param  int  $threshold   Max allowed executions
     * @param  int  $windowSecs  Time window in seconds
     * @return bool
     */
    public function exceedsFloodThreshold(int $threshold, int $windowSecs): bool
    {
        $count     = $this->execution_count ?? 0;
        $lastAt    = $this->last_executed_at;

        if (! $lastAt) {
            return false;
        }

        $secondsSinceLast = now()->diffInSeconds($lastAt);

        // Reset window if outside
        if ($secondsSinceLast > $windowSecs) {
            return false;
        }

        return $count >= $threshold;
    }

    /**
     * Record a deduplication fingerprint.
     *
     * @param  string  $dedupKey
     * @param  int     $ttlSeconds
     * @return void
     */
    public function recordDeduplication(string $dedupKey, int $ttlSeconds = 60): void
    {
        $fingerprints = $this->execution_fingerprint ?? [];

        $fingerprints[$dedupKey] = [
            'executed_at' => time(),
            'ttl'         => $ttlSeconds,
        ];

        $this->update(['execution_fingerprint' => $fingerprints]);
    }

    /**
     * Record a loop-detection fingerprint.
     *
     * @param  string  $loopKey
     * @return void
     */
    public function recordLoop(string $loopKey): void
    {
        $fingerprints = $this->execution_fingerprint ?? [];

        $fingerprints[$loopKey] = [
            'executed_at' => time(),
        ];

        $this->update(['execution_fingerprint' => $fingerprints]);
    }

    /**
     * Increment the flood counter and update last executed timestamp.
     *
     * @return void
     */
    public function recordFlood(): void
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }

    // =====================================================================
    // Existing helpers
    // =====================================================================

    public static function makeFingerprint(
        int $leadId,
        string $triggerType,
        string $event,
        ?string $actionHash = null
    ): string {
        $parts = [
            'lead_id'      => $leadId,
            'trigger_type' => $triggerType,
            'event'        => $event,
            'action_hash'  => $actionHash ?? 'global',
        ];

        return md5(json_encode($parts));
    }

    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isBlocked(): bool
    {
        return ! empty($this->blocked_reason);
    }

    public function incrementHit(): void
    {
        $this->increment('hit_count');
        $this->update(['last_executed_at' => now()]);
    }

    public function markBlocked(string $reason): void
    {
        $this->update(['blocked_reason' => $reason]);
    }
}
