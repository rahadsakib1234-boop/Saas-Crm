<?php

namespace Webkul\Lead\Observers;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadTemperatureClassifier;
use Webkul\Lead\Services\LeadAutomationRuleEngine;

/**
 * Lead Observer
 *
 * Handles automatic actions when leads are created or updated.
 * Uses two systems:
 *   1. LeadTemperatureClassifier - scoring-based temperature (hot/warm/cold)
 *      → Executes multiple actions per threshold (tag + notify + task + etc.)
 *   2. LeadAutomationRuleEngine   - custom behavior rules from database
 */
class LeadObserver
{
    /**
     * Temperature tag names (used to clean up old tags on re-classify).
     */
    protected const TEMPERATURE_TAGS = ['hot', 'warm', 'cold'];

    public function __construct(
        protected LeadTemperatureClassifier $classifier,
        protected LeadAutomationRuleEngine $ruleEngine
    ) {}

    /**
     * Handle the Lead created event.
     */
    public function created(Lead $lead): void
    {
        $this->runTemperatureScoring($lead);
        $this->runAutomationRules($lead, 'created');
    }

    /**
     * Handle the Lead updated event.
     */
    public function updated(Lead $lead): void
    {
        if ($this->shouldReclassify($lead)) {
            if (config('lead_temperature.replace_on_update', true)) {
                $this->runTemperatureScoring($lead);
            }
        }

        $this->runAutomationRules($lead, 'updated');
    }

    /**
     * Run scoring-based temperature classification and execute all actions.
     *
     * This now handles:
     *   - Tagging (add/remove temperature tags)
     *   - Notifications (alert agent)
     *   - Tasks (create follow-up tasks)
     *   - Activities (schedule calls/meetings)
     *   - Webhooks (notify external systems)
     */
    protected function runTemperatureScoring(Lead $lead): void
    {
        // classify() now executes ALL actions for the matched threshold
        // Returns array of action results: [['action' => 'add_tag', 'result' => Tag], ...]
        $this->classifier->classify($lead);
    }

    /**
     * Run automation rules from database.
     */
    protected function runAutomationRules(Lead $lead, string $event): void
    {
        $this->ruleEngine->evaluate($lead, $event);
    }

    /**
     * Determine if the lead should be re-classified.
     */
    protected function shouldReclassify(Lead $lead): bool
    {
        $dirtyFields = array_keys($lead->getDirty());
        $analyzeFields = config('lead_temperature.analyze_fields', ['description', 'title']);

        return ! empty(array_intersect($dirtyFields, $analyzeFields));
    }

    /**
     * Get IDs of all temperature tags.
     *
     * @return array<int>
     */
    protected function getTemperatureTagIds(): array
    {
        return \Webkul\Tag\Models\Tag::whereIn('name', self::TEMPERATURE_TAGS)->pluck('id')->toArray();
    }
}