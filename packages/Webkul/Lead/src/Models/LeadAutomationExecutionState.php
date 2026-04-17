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
 *
 * Key features:
 *   - Unique fingerprint per lead+trigger+action combination
 *   - Hit counter for loop detection
 *   - TTL-based expiration for auto-cleanup
 *   - Guard results stored for debugging
 *
 * @see AutomationGuard
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
        'first_executed_at',
        'last_executed_at',
        'expires_at',
        'guard_results',
        'blocked_reason',
    ];

    protected $casts = [
        'hit_count' => 'integer',
        'guard_results' => 'array',
        'first_executed_at' => 'datetime',
        'last_executed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the lead this state belongs to.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user this state belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Generate a fingerprint for lead+trigger+event.
     */
    public static function makeFingerprint(
        int $leadId,
        string $triggerType,
        string $event,
        ?string $actionHash = null
    ): string {
        $parts = [
            'lead_id' => $leadId,
            'trigger_type' => $triggerType,
            'event' => $event,
            'action_hash' => $actionHash ?? 'global',
        ];

        return md5(json_encode($parts));
    }

    /**
     * Check if this fingerprint exists and is still valid.
     */
    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if blocked by guard rules.
     */
    public function isBlocked(): bool
    {
        return ! empty($this->blocked_reason);
    }

    /**
     * Increment hit count atomically.
     */
    public function incrementHit(): void
    {
        $this->increment('hit_count');
        $this->update(['last_executed_at' => now()]);
    }

    /**
     * Mark as blocked with reason.
     */
    public function markBlocked(string $reason): void
    {
        $this->update(['blocked_reason' => $reason]);
    }
}
