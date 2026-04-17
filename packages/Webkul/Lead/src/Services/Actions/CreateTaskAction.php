<?php

namespace Webkul\Lead\Services\Actions;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;

/**
 * Create Task Action
 *
 * Creates a follow-up task for the lead.
 */
class CreateTaskAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        if (! $context instanceof Lead) {
            throw new \InvalidArgumentException('CreateTaskAction requires Lead context');
        }

        $title = $params['title'] ?? 'Follow up with lead';
        $dueOffset = $params['due_offset_hours'] ?? 24; // hours from now
        $assignedTo = $params['assigned_to'] ?? $context->user_id;
        $description = $params['description'] ?? null;

        // Interpolate placeholders
        $title = $this->interpolate($title, $context);
        $description = $description ? $this->interpolate($description, $context) : null;

        $dueDate = Carbon::now()->addHours($dueOffset);

        // Create the task via activity system
        $task = Activity::create([
            'title' => $title,
            'description' => $description,
            'type' => 'task',
            'status' => 'pending',
            'user_id' => $assignedTo,
            'lead_id' => $context->id,
            'due_date' => $dueDate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'task_id' => $task->id,
            'title' => $title,
            'due_date' => $dueDate->toDateTimeString(),
            'assigned_to' => $assignedTo,
        ];
    }

    public function name(): string
    {
        return 'create_task';
    }

    public function validate(array $params): bool
    {
        return isset($params['title']);
    }

    public function requiredParams(): array
    {
        return ['title'];
    }

    protected function interpolate(string $template, Lead $lead): string
    {
        $replacements = [
            '{lead_title}' => $lead->title,
            '{lead_id}' => $lead->id,
            '{person_name}' => $lead->person?->name ?? 'Unknown',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
