<?php

namespace Webkul\Lead\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Lead\Contracts\LeadAutomationRuleCondition as LeadAutomationRuleConditionContract;

class LeadAutomationRuleCondition extends Model implements LeadAutomationRuleConditionContract
{
    protected $table = 'lead_automation_rule_conditions';

    protected $fillable = [
        'rule_id',
        'field',
        'operator',
        'value',
        'condition_order',
    ];

    /**
     * Get the rule that owns this condition.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(LeadAutomationRule::class, 'rule_id');
    }

    /**
     * Evaluate this condition against a lead.
     */
    public function evaluate(Lead $lead): bool
    {
        $fieldValue = $this->getFieldValue($lead);
        $compareValue = $this->value;

        return match ($this->operator) {
            'equals' => strtolower((string) $fieldValue) === strtolower((string) $compareValue),
            'not_equals' => strtolower((string) $fieldValue) !== strtolower((string) $compareValue),
            'contains' => str_contains(strtolower((string) $fieldValue), strtolower((string) $compareValue)),
            'not_contains' => ! str_contains(strtolower((string) $fieldValue), strtolower((string) $compareValue)),
            'starts_with' => str_starts_with(strtolower((string) $fieldValue), strtolower((string) $compareValue)),
            'ends_with' => str_ends_with(strtolower((string) $fieldValue), strtolower((string) $compareValue)),
            'is_empty' => empty(trim((string) $fieldValue)),
            'is_not_empty' => ! empty(trim((string) $fieldValue)),
            'greater_than' => is_numeric($fieldValue) && is_numeric($compareValue) && (float) $fieldValue > (float) $compareValue,
            'less_than' => is_numeric($fieldValue) && is_numeric($compareValue) && (float) $fieldValue < (float) $compareValue,
            default => false,
        };
    }

    /**
     * Get the value of the specified field from the lead.
     *
     * @return mixed
     */
    protected function getFieldValue(Lead $lead)
    {
        // Handle nested attribute values (custom fields)
        if (str_starts_with($this->field, 'attr.')) {
            $attributeCode = substr($this->field, 5);

            return $lead->getAttributeValue($attributeCode);
        }

        return $lead->{$this->field} ?? null;
    }
}
