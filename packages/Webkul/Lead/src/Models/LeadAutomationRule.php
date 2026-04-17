<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Lead\Contracts\LeadAutomationRule as LeadAutomationRuleContract;

class LeadAutomationRule extends Model implements LeadAutomationRuleContract
{
    protected $table = 'lead_automation_rules';

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'priority',
        'entity_type',
        'trigger_config',
        'actions',
        'condition_logic',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Get the conditions for this rule.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(LeadAutomationRuleCondition::class, 'rule_id')->orderBy('condition_order');
    }

    /**
     * Check if rule matches the given lead.
     */
    public function matchesLead(Lead $lead): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $conditions = $this->conditions;

        if ($conditions->isEmpty()) {
            return false;
        }

        $results = $conditions->map(function ($condition) use ($lead) {
            return $condition->evaluate($lead);
        });

        return $this->condition_logic === 'and'
            ? $results->every(fn($r) => $r === true)
            : $results->contains(true);
    }

    /**
     * Execute rule actions on a lead.
     */
    public function execute(Lead $lead): void
    {
        $actions = $this->actions ?? [];

        foreach ($actions as $action) {
            $this->executeAction($action, $lead);
        }
    }

    /**
     * Execute a single action.
     */
    protected function executeAction(array $action, Lead $lead): void
    {
        match ($action['action'] ?? '') {
            'add_tag' => $this->actionAddTag($action['params'] ?? [], $lead),
            'remove_tag' => $this->actionRemoveTag($action['params'] ?? [], $lead),
            'set_stage' => $this->actionSetStage($action['params'] ?? [], $lead),
            'set_status' => $this->actionSetStatus($action['params'] ?? [], $lead),
            default => null,
        };
    }

    protected function actionAddTag(array $params, Lead $lead): void
    {
        $tagName = $params['tag'] ?? null;
        if (! $tagName) {
            return;
        }

        $tag = \Webkul\Tag\Models\Tag::where('name', $tagName)->first();
        if ($tag) {
            $lead->tags()->syncWithoutDetaching([$tag->id]);
        }
    }

    protected function actionRemoveTag(array $params, Lead $lead): void
    {
        $tagName = $params['tag'] ?? null;
        if (! $tagName) {
            return;
        }

        $tag = \Webkul\Tag\Models\Tag::where('name', $tagName)->first();
        if ($tag) {
            $lead->tags()->detach($tag->id);
        }
    }

    protected function actionSetStage(array $params, Lead $lead): void
    {
        $stageId = $params['stage_id'] ?? null;
        if ($stageId) {
            $lead->lead_pipeline_stage_id = $stageId;
            $lead->save();
        }
    }

    protected function actionSetStatus(array $params, Lead $lead): void
    {
        $status = $params['status'] ?? null;
        if ($status) {
            $lead->status = $status;
            $lead->save();
        }
    }
}