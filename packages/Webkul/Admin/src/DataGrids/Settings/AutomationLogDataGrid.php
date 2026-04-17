<?php

namespace Webkul\Admin\DataGrids\Settings;

use Illuminate\Support\Facades\DB;
use Webkul\Admin\DataGrids\DataGrid;

class AutomationLogDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('lead_automation_logs')
            ->select(
                'lead_automation_logs.id',
                'lead_automation_logs.lead_id',
                'leads.title as lead_title',
                'lead_automation_logs.rule_name',
                'lead_automation_logs.trigger_event',
                'lead_automation_logs.score',
                'lead_automation_logs.actions_executed',
                'lead_automation_logs.status',
                'lead_automation_logs.error_message',
                'lead_automation_logs.executed_at'
            )
            ->leftJoin('leads', 'lead_automation_logs.lead_id', '=', 'leads.id');

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function addColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => 'ID',
            'type' => 'number',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'lead_title',
            'label' => 'Lead',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return '<a href="/admin/leads/'.$row->lead_id.'" class="text-brandColor hover:underline">'.e($row->lead_title).'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'rule_name',
            'label' => 'Rule',
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'trigger_event',
            'label' => 'Trigger',
            'type' => 'string',
            'sortable' => true,
            'closure' => function ($row) {
                return '<span class="px-2 py-1 text-xs rounded bg-gray-100 dark:bg-gray-800">'.ucfirst($row->trigger_event).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'score',
            'label' => 'Score',
            'type' => 'number',
            'sortable' => true,
            'closure' => function ($row) {
                $color = $row->score >= 15 ? 'red' : ($row->score >= 5 ? 'orange' : 'blue');

                return '<span class="px-2 py-1 text-xs rounded bg-'.$color.'-100 text-'.$color.'-700 font-bold">'.($row->score >= 0 ? '+' : '').$row->score.'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => 'Status',
            'type' => 'string',
            'sortable' => true,
            'closure' => function ($row) {
                return match ($row->status) {
                    'success' => '<span class="text-green-600">✓ Success</span>',
                    'failed' => '<span class="text-red-600">✗ Failed</span>',
                    'skipped' => '<span class="text-yellow-600">⚠ Skipped</span>',
                    default => '<span class="text-gray-400">'.ucfirst($row->status).'</span>',
                };
            },
        ]);

        $this->addColumn([
            'index' => 'executed_at',
            'label' => 'Executed At',
            'type' => 'datetime',
            'sortable' => true,
        ]);
    }
}
