<?php

namespace Webkul\Lead\Services\Actions;

use Carbon\Carbon;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;

/**
 * Create Task Action
 *
 * Creates a follow-up task for the lead via the Activity system.
 *
 * Activity model columns (Krayin core):
 *   - title, comment, type, is_done, user_id, schedule_from, schedule_to
 *   - Leads are linked via lead_activities pivot (leads() BelongsToMany)
 *   - NO: due_date, lead_id, status — those columns don't exist on activities table
 */
class CreateTaskAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        if (! $context instanceof Lead) {
            throw new \InvalidArgumentException('CreateTaskAction requires Lead context');
        }

        if (! class_exists(Activity::class)) {
            return null;
        }

        $title = $params['title'] ?? 'Follow up with lead';
        $dueOffset = $params['due_offset_hours'] ?? 24;
        $assignedTo = $params['assigned_to'] ?? $context->user_id;
        $description = $params['description'] ?? null;

        $title = $this->interpolate($title, $context);
        $description = $description ? $this->interpolate($description, $context) : null;

        $scheduleFrom = Carbon::now();
        // FIX: use schedule_from / schedule_to (Activity's actual columns)
        $scheduleTo = Carbon::now()->addHours($dueOffset);

        $activity = Activity::create([
            'title' => $title,
            'comment' => $description,
            'type' => 'task',
            'is_done' => 0,
            'user_id' => $assignedTo,
            'schedule_from' => $scheduleFrom,
            'schedule_to' => $scheduleTo,
        ]);

        // FIX: link to lead via pivot table (lead_activities), not a lead_id column
        if ($activity) {
            $activity->leads()->attach($context->id);
        }

        return [
            'task_id' => $activity?->id,
            'title' => $title,
            'due_at' => $scheduleTo->toDateTimeString(),
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
