<?php

namespace Webkul\Lead\Repositories;

use Illuminate\Support\Collection;
use Webkul\Lead\Models\LeadAutomationRule;
use Webkul\Lead\Models\LeadAutomationRuleCondition;
use Webkul\Core\Eloquent\Repository;

class LeadAutomationRuleRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return LeadAutomationRule::class;
    }

    /**
     * Create rule with conditions.
     */
    public function create(array $data): LeadAutomationRule
    {
        $rule = parent::create([
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'is_active'      => $data['is_active'] ?? true,
            'priority'       => $data['priority'] ?? 0,
            'trigger_event'  => $data['trigger_event'] ?? 'created',
            'condition_logic'=> $data['condition_logic'] ?? 'and',
            'actions'        => $data['actions'] ?? [],
        ]);

        if (! empty($data['conditions'])) {
            foreach ($data['conditions'] as $index => $condition) {
                $rule->conditions()->create([
                    'field'          => $condition['field'],
                    'operator'       => $condition['operator'],
                    'value'          => $condition['value'],
                    'condition_order'=> $index,
                ]);
            }
        }

        return $rule;
    }

    /**
     * Update rule with conditions.
     */
    public function update(array $data, $id): LeadAutomationRule
    {
        $rule = $this->findOrFail($id);

        $rule->update([
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'is_active'      => $data['is_active'] ?? $rule->is_active,
            'priority'       => $data['priority'] ?? $rule->priority,
            'trigger_event'  => $data['trigger_event'] ?? $rule->trigger_event,
            'condition_logic'=> $data['condition_logic'] ?? $rule->condition_logic,
            'actions'        => $data['actions'] ?? $rule->actions,
        ]);

        if (isset($data['conditions'])) {
            $rule->conditions()->delete();

            foreach ($data['conditions'] as $index => $condition) {
                $rule->conditions()->create([
                    'field'          => $condition['field'],
                    'operator'       => $condition['operator'],
                    'value'          => $condition['value'],
                    'condition_order'=> $index,
                ]);
            }
        }

        return $rule;
    }
}