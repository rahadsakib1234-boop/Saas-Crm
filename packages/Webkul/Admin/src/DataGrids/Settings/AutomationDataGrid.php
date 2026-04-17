<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Support\Facades\DB;
use Webkul\Admin\DataGrids\DataGrid;
use Webkul\Lead\Repositories\LeadAutomationRuleRepository;

class AutomationDataGrid extends DataGrid
{
    protected $repository;

    public function __construct(
        LeadAutomationRuleRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('lead_automation_rules')
            ->addSelect(
                'id',
                'name',
                'description',
                'is_active',
                'priority',
                'trigger_event',
                'created_at'
            );

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function addColumns()
    {
        $this->addColumn([
            'index'    => 'id',
            'label'    => 'ID',
            'type'     => 'number',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'     => 'Name',
            'type'      => 'string',
            'sortable'  => true,
            'filterable'=> true,
            'closure'   => function ($row) {
                return '<span class="font-medium">' . $row->name . '</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'trigger_event',
            'label'     => 'Trigger',
            'type'      => 'string',
            'sortable'  => true,
            'filterable'=> true,
            'closure'   => function ($row) {
                $colors = [
                    'created' => 'green',
                    'updated' => 'blue',
                    'tagged'  => 'purple',
                ];
                $color = $colors[$row->trigger_event] ?? 'gray';
                return '<span class="px-2 py-1 text-xs rounded bg-' . $color . '-100 text-' . $color . '-700">' . ucfirst($row->trigger_event) . '</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'is_active',
            'label'     => 'Status',
            'type'      => 'boolean',
            'sortable'  => true,
            'filterable'=> true,
            'closure'   => function ($row) {
                return $row->is_active
                    ? '<span class="text-green-600">● Active</span>'
                    : '<span class="text-gray-400">○ Inactive</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'priority',
            'label'     => 'Priority',
            'type'      => 'number',
            'sortable'  => true,
            'filterable'=> true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'     => 'Created',
            'type'      => 'date',
            'sortable'  => true,
            'filterable'=> true,
        ]);
    }

    /**
     * Prevent actions for unauthorized users.
     */
    public function canActions(): bool
    {
        return bouncer()->hasPermission('settings.automation.rules.edit');
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => 'Edit Rule',
            'method' => 'GET',
            'route'  => 'admin.settings.automation.edit',
            'params'  => ['id'],
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete Rule',
            'method' => 'DELETE',
            'route'  => 'admin.settings.automation.destroy',
            'params'  => ['id'],
        ]);
    }
}