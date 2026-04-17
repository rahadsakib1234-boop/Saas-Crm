<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Collection;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAutomationRule;

/**
 * Lead Automation Rule Engine
 *
 * Evaluates all active rules against a lead and executes matching actions.
 * Rules are sorted by priority (lower number = higher priority).
 *
 * Delegates to LeadAutomationRule::matchesLead() and ->execute() for core logic
 * to avoid duplication between the service and model.
 */
class LeadAutomationRuleEngine
{
    /**
     * Evaluate and execute all matching rules for a lead.
     *
     * @param  Lead  $lead
     * @param  string  $event  'created' | 'updated'
     * @return Collection<LeadAutomationRule>  Rules that matched
     */
    public function evaluate(Lead $lead, string $event): Collection
    {
        $matchedRules = collect();

        $rules = LeadAutomationRule::where('is_active', 1)
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($rules as $rule) {
            if ($this->shouldFire($rule, $event) && $rule->matchesLead($lead)) {
                $rule->execute($lead);
                $matchedRules->push($rule);
            }
        }

        return $matchedRules;
    }

    /**
     * Check if rule should fire on the given event.
     */
    protected function shouldFire(LeadAutomationRule $rule, string $event): bool
    {
        $triggerConfig = $rule->trigger_config ?? [];
        $events = $triggerConfig['events'] ?? ['created', 'updated'];

        return in_array($event, $events);
    }
}