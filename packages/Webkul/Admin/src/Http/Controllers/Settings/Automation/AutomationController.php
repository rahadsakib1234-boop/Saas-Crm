<?php

namespace Webkul\Admin\Http\Controllers\Settings\Automation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\DataGrids\Settings\AutomationDataGrid;
use Webkul\Admin\DataGrids\Settings\AutomationLogDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Lead\Repositories\LeadAutomationRuleRepository;

class AutomationController extends Controller
{
    public function __construct(
        protected LeadAutomationRuleRepository $ruleRepository
    ) {
    }

    public function index()
    {
        if (request()->ajax()) {
            return app(AutomationDataGrid::class)->toJson();
        }

        return view('admin::settings.automation.index');
    }

    public function create()
    {
        return view('admin::settings.automation.create');
    }

    public function edit(int $id)
    {
        return view('admin::settings.automation.edit', [
            'rule' => $this->ruleRepository->findOrFail($id),
        ]);
    }

    public function temperature()
    {
        return view('admin::settings.automation.temperature');
    }

    public function logs()
    {
        if (request()->ajax()) {
            return app(AutomationLogDataGrid::class)->toJson();
        }

        return view('admin::settings.automation.logs');
    }

    public function outcomes()
    {
        return view('admin::settings.automation.outcomes');
    }

    public function store(Request $request)
    {
        $data = $this->validateRuleRequest($request);
        $this->normalizeActions($data);

        $rule = $this->ruleRepository->create($data);

        Event::dispatch('lead.automation.rule.created', $rule);

        return redirect()->route('admin.settings.automation.index')->with('success', 'Automation rule created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validateRuleRequest($request);
        $this->normalizeActions($data);

        $rule = $this->ruleRepository->update($data, $id);

        Event::dispatch('lead.automation.rule.updated', $rule);

        return redirect()->route('admin.settings.automation.index')->with('success', 'Automation rule updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->ruleRepository->delete($id);

        Event::dispatch('lead.automation.rule.deleted', $id);

        return redirect()->route('admin.settings.automation.index')->with('success', 'Automation rule deleted successfully.');
    }

    public function toggle(int $id)
    {
        $rule = $this->ruleRepository->findOrFail($id);
        $rule->update(['is_active' => ! $rule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
        ]);
    }

    protected function validateRuleRequest(Request $request): array
    {
        return $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer',
            'trigger_event' => 'required|string|in:created,updated',
            'condition_logic' => 'required|string|in:and,or',
            'is_active' => 'sometimes|boolean',
            'conditions' => 'nullable|array',
            'conditions.*.field' => 'required_with:conditions|string|max:255',
            'conditions.*.operator' => 'required_with:conditions|string|max:50',
            'conditions.*.value' => 'nullable|string|max:255',
            'actions' => 'nullable|array',
            'actions.*.action' => 'required_with:actions|string|max:255',
            'actions.*.value' => 'nullable|string|max:255',
        ]);
    }

    protected function normalizeActions(array &$data): void
    {
        $actions = collect($data['actions'] ?? [])
            ->filter(fn ($action) => filled($action['action'] ?? null))
            ->map(function (array $action) {
                $value = trim((string) ($action['value'] ?? ''));

                return [
                    'action' => $action['action'],
                    'params' => match ($action['action']) {
                        'add_tag', 'remove_tag' => ['tag' => $value],
                        'notify' => ['message' => $value],
                        'create_task' => ['title' => $value],
                        'move_to_stage' => ['stage_id' => $value],
                        'webhook' => ['url' => $value],
                        default => ['value' => $value],
                    },
                ];
            })
            ->values()
            ->all();

        $conditions = collect($data['conditions'] ?? [])
            ->filter(fn ($condition) => filled($condition['field'] ?? null) && filled($condition['operator'] ?? null))
            ->values()
            ->all();

        $data['actions'] = $actions;
        $data['conditions'] = $conditions;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
    }
}
