<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Log;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\LeadAutomationLog;
use Webkul\Tag\Models\Tag;

/**
 * Lead Temperature Classifier (Scoring-Based)
 *
 * Classifies leads based on a configurable scoring system.
 * Conditions add or subtract points; thresholds trigger multiple actions.
 *
 * Each execution:
 *   1. Checks guards (duplicate/loop/flood)
 *   2. Logs execution start
 *   3. Calculates score
 *   4. Executes all threshold actions
 *   5. Logs completion with results
 *
 * @see config/lead_temperature.php
 */
class LeadTemperatureClassifier
{
    protected const TRIGGER_TYPE = 'temperature_scoring';
    protected const TRIGGER_NAME = 'LeadTemperatureScoring';

    public function __construct(
        protected AutomationGuard $guard,
        protected LeadActionExecutor $executor
    ) {}

    /**
     * Classify a lead's temperature and execute all threshold actions.
     *
     * @param  Lead  $lead
     * @return array  Results of all executed actions
     */
    public function classify(Lead $lead): array
    {
        // Step 1: Guard check
        $guardFailures = $this->guard->check($lead, 'temperature_scoring');

        // Step 2: Create execution log
        $log = $this->createLog($lead, $guardFailures);

        if (! empty($guardFailures)) {
            $log->markPartial('Guard blocked: ' . json_encode($guardFailures));

            return ['blocked' => true, 'reasons' => $guardFailures];
        }

        // Step 3: Calculate score
        $score             = $this->calculateScore($lead);
        $matchedConditions = $this->getMatchedConditions($lead);

        // Step 4: Determine threshold
        $threshold = $this->determineThreshold($score);

        if (! $threshold) {
            $log->markSuccess();
            $log->update([
                'context' => array_merge($log->context ?? [], [
                    'score'     => $score,
                    'threshold' => null,
                ]),
            ]);

            return ['blocked' => false, 'score' => $score, 'threshold' => null, 'actions' => []];
        }

        // Step 5: Execute all actions
        // FIX: executeAll() only accepts (array $actions, Lead $lead) — no $log parameter
        $results = $this->executor->executeAll($threshold['actions'] ?? [], $lead);

        // Step 6: Record guard state
        $this->guard->record($lead, 'temperature_scoring');

        // Step 7: Update log with results
        $log->update([
            'context' => array_merge($log->context ?? [], [
                'score'                => $score,
                'matched_conditions'   => $matchedConditions,
                'threshold_min_score'  => $threshold['min_score'] ?? null,
            ]),
            'actions_executed' => $results,
        ]);

        $log->markSuccess();

        return [
            'blocked'            => false,
            'score'              => $score,
            'matched_conditions' => $matchedConditions,
            'threshold'          => $threshold['min_score'] ?? null,
            'actions'            => $results,
        ];
    }

    /**
     * Calculate total score for a lead.
     */
    public function calculateScore(Lead $lead): int
    {
        $text       = $this->buildSearchText($lead);
        $conditions = config('lead_temperature.conditions', []);
        $totalScore = 0;

        foreach ($conditions as $condition) {
            if ($this->conditionMatches($text, $condition)) {
                $totalScore += ($condition['points'] ?? 0);
            }
        }

        return $totalScore;
    }

    /**
     * Get list of matched condition descriptions.
     *
     * @return array<array>
     */
    public function getMatchedConditions(Lead $lead): array
    {
        $text       = $this->buildSearchText($lead);
        $conditions = config('lead_temperature.conditions', []);
        $matched    = [];

        foreach ($conditions as $condition) {
            if ($this->conditionMatches($text, $condition)) {
                $matched[] = [
                    'field'    => $condition['field'] ?? '',
                    'operator' => $condition['operator'] ?? '',
                    'value'    => $condition['value'] ?? '',
                    'points'   => $condition['points'] ?? 0,
                ];
            }
        }

        return $matched;
    }

    /**
     * Determine which threshold matches the score.
     */
    public function determineThreshold(int $score): ?array
    {
        $thresholds = config('lead_temperature.thresholds', []);

        foreach ($thresholds as $threshold) {
            $minScore = $threshold['min_score'] ?? -9999;

            if ($score >= $minScore) {
                return $threshold;
            }
        }

        return null;
    }

    /**
     * Get the tag model for a temperature level.
     */
    public function getTemperatureTag(string $temperature): ?Tag
    {
        return Tag::where('name', $temperature)->first();
    }

    /**
     * Build concatenated lowercase text from configured fields.
     */
    protected function buildSearchText(Lead $lead): string
    {
        $fields    = config('lead_temperature.analyze_fields', ['description', 'title']);
        $textParts = [];

        foreach ($fields as $field) {
            if (isset($lead->{$field})) {
                $textParts[] = mb_strtolower((string) $lead->{$field});
            }
        }

        return implode(' ', $textParts);
    }

    /**
     * Check if a condition matches the search text.
     */
    protected function conditionMatches(string $text, array $condition): bool
    {
        $operator = $condition['operator'] ?? 'contains';
        $value    = mb_strtolower((string) ($condition['value'] ?? ''));

        return match ($operator) {
            'contains'     => mb_strpos($text, $value) !== false,
            'not_contains' => mb_strpos($text, $value) === false,
            'equals'       => $text === $value,
            'not_equals'   => $text !== $value,
            'starts_with'  => mb_strpos($text, $value) === 0,
            'ends_with'    => mb_strlen($text) >= mb_strlen($value)
                                && mb_substr($text, -mb_strlen($value)) === $value,
            'is_empty'     => mb_trim($text) === '',
            'is_not_empty' => mb_trim($text) !== '',
            'greater_than' => is_numeric($text) && is_numeric($value) && (float) $text > (float) $value,
            'less_than'    => is_numeric($text) && is_numeric($value) && (float) $text < (float) $value,
            default        => false,
        };
    }

    /**
     * Create an automation log entry.
     */
    protected function createLog(Lead $lead, array $guardFailures = []): LeadAutomationLog
    {
        $dirtyFields = array_keys($lead->getDirty());

        return LeadAutomationLog::create([
            'lead_id'          => $lead->id,
            'user_id'          => $lead->user_id,
            'trigger_type'     => self::TRIGGER_TYPE,
            'trigger_name'     => self::TRIGGER_NAME,
            'event'            => $lead->wasRecentlyCreated ? 'created' : 'updated',
            'context'          => [
                'dirty_fields'  => $dirtyFields,
                'guard_failures' => $guardFailures,
            ],
            'actions_executed' => [],
            'status'           => 'pending',
        ]);
    }
}
