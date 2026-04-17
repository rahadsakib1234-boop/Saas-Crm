<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\LeadAutomationLog as LeadAutomationLogContract;

class LeadAutomationLog extends Model implements LeadAutomationLogContract
{
    protected $table = 'lead_automation_logs';

    protected $fillable = [
        'lead_id',
        'user_id',
        'trigger_type',
        'trigger_name',
        'event',
        'context',
        'actions_executed',
        'status',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'context' => 'array',
        'actions_executed' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the lead this log belongs to.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who owned the lead at execution time.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProxy::modelClass());
    }

    /**
     * Mark log as successful.
     */
    public function markSuccess(): void
    {
        $this->update(['status' => 'success']);
    }

    /**
     * Mark log as partially failed.
     */
    public function markPartial(string $error): void
    {
        $this->update(['status' => 'partial', 'error_message' => $error]);
    }

    /**
     * Mark log as fully failed.
     */
    public function markFailed(string $error): void
    {
        $this->update(['status' => 'failed', 'error_message' => $error]);
    }

    /**
     * Add an executed action to the log.
     */
    public function addAction(string $action, array $params, $result = null, ?string $error = null): void
    {
        $actions = $this->actions_executed ?? [];
        
        $actions[] = [
            'action' => $action,
            'params' => $params,
            'result' => $result ? get_class($result) : null,
            'error' => $error,
            'executed_at' => now()->toIso8601String(),
        ];
        
        $this->update(['actions_executed' => $actions]);
    }
}