<?php

namespace Webkul\Lead\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Activity\Models\Activity;
use Webkul\Email\Models\Email;
use Webkul\Lead\Models\Lead;
use Webkul\Tag\Models\Tag;

/**
 * Lead Action Executor
 *
 * Executes automation actions triggered by temperature scoring or rules.
 *
 * Supported actions:
 *   - add_tag           → Attach tag to lead
 *   - remove_tag        → Remove tag from lead
 *   - update_field      → Update lead field
 *   - notify_agent      → Send in-app notification (via LeadNotificationService)
 *   - create_task       → Create follow-up task (Activity)
 *   - schedule_activity → Schedule call/meeting (Activity)
 *   - send_email        → Send templated email
 *   - webhook           → POST to external URL
 */
class LeadActionExecutor
{
    // FIX: constructor now correctly accepts LeadNotificationService (not AutomationGuard)
    public function __construct(
        protected LeadNotificationService $notificationService = new LeadNotificationService
    ) {}

    /**
     * Execute a single action for a lead.
     */
    public function execute(string $actionType, array $params, Lead $lead): mixed
    {
        return match ($actionType) {
            'add_tag' => $this->addTag($params, $lead),
            'remove_tag' => $this->removeTag($params, $lead),
            'update_field' => $this->updateField($params, $lead),
            'notify_agent' => $this->notifyAgent($params, $lead),
            'create_task' => $this->createTask($params, $lead),
            'schedule_activity' => $this->scheduleActivity($params, $lead),
            'send_email' => $this->sendEmail($params, $lead),
            'webhook' => $this->triggerWebhook($params, $lead),
            default => null,
        };
    }

    /**
     * Execute multiple actions from a threshold config.
     *
     * FIX: removed the unused $log parameter that was causing an arity mismatch.
     * The $log is updated by LeadTemperatureClassifier after this returns.
     *
     * @return array Results for each action
     */
    public function executeAll(array $actions, Lead $lead): array
    {
        $results = [];

        foreach ($actions as $action) {
            $actionType = $action['action'] ?? null;
            $params = $action['params'] ?? [];

            if (! $actionType) {
                continue;
            }

            try {
                $result = $this->execute($actionType, $params, $lead);
                $results[] = [
                    'action' => $actionType,
                    'result' => $result,
                    'error' => null,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'action' => $actionType,
                    'result' => null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    // =========================================================================
    // TAG ACTIONS
    // =========================================================================

    protected function addTag(array $params, Lead $lead): ?Tag
    {
        $tagName = $params['tag'] ?? null;
        if (! $tagName) {
            return null;
        }

        $tag = Tag::firstOrCreate(['name' => $tagName]);
        $lead->tags()->syncWithoutDetaching([$tag->id]);

        return $tag;
    }

    protected function removeTag(array $params, Lead $lead): bool
    {
        $tagName = $params['tag'] ?? null;
        if (! $tagName) {
            return false;
        }

        $tag = Tag::where('name', $tagName)->first();
        if ($tag) {
            $lead->tags()->detach($tag->id);

            return true;
        }

        return false;
    }

    // =========================================================================
    // FIELD UPDATE
    // =========================================================================

    protected function updateField(array $params, Lead $lead): bool
    {
        $field = $params['field'] ?? null;
        $value = $params['value'] ?? null;

        if (! $field) {
            return false;
        }

        $lead->update([$field => $value]);

        return true;
    }

    // =========================================================================
    // NOTIFICATIONS
    // =========================================================================

    protected function notifyAgent(array $params, Lead $lead): bool
    {
        $title = $params['title'] ?? 'Lead Update';
        $body = $params['body'] ?? 'A lead requires your attention';
        $priority = $params['priority'] ?? 'medium';

        if (! $lead->user_id) {
            return false;
        }

        return $this->notificationService->send(
            $lead->user_id,
            $title,
            $body,
            $priority,
            $lead
        );
    }

    // =========================================================================
    // TASKS / ACTIVITIES
    // FIX: use Activity's actual columns: schedule_from, schedule_to
    //      Activity does NOT have due_date, lead_id, or status columns.
    //      Link via the lead_activities pivot table instead.
    // =========================================================================

    protected function createTask(array $params, Lead $lead): ?object
    {
        $title = $params['title'] ?? 'Follow up required';
        $description = $params['description'] ?? null;
        $dueDays = $params['due_days'] ?? 1;

        if (! class_exists(Activity::class)) {
            return null;
        }

        $scheduleFrom = Carbon::now();
        $scheduleTo = Carbon::now()->addDays($dueDays);

        $activity = Activity::create([
            'title' => $title,
            'comment' => $description,
            'type' => 'task',
            'is_done' => 0,
            'user_id' => $lead->user_id,
            'schedule_from' => $scheduleFrom,
            'schedule_to' => $scheduleTo,
        ]);

        // Link to the lead via pivot
        if ($activity) {
            $activity->leads()->attach($lead->id);
        }

        return $activity;
    }

    protected function scheduleActivity(array $params, Lead $lead): ?object
    {
        $title = $params['title'] ?? 'Scheduled Follow-up';
        $type = $params['type'] ?? 'call';
        $daysAhead = $params['days_ahead'] ?? 1;
        $comment = $params['description'] ?? null;

        if (! class_exists(Activity::class)) {
            return null;
        }

        $scheduleFrom = Carbon::now()->addDays($daysAhead);
        $scheduleTo = $scheduleFrom->copy()->addHour();

        $activity = Activity::create([
            'title' => $title,
            'comment' => $comment,
            'type' => $type,
            'is_done' => 0,
            'user_id' => $lead->user_id,
            'schedule_from' => $scheduleFrom,
            'schedule_to' => $scheduleTo,
        ]);

        if ($activity) {
            $activity->leads()->attach($lead->id);
        }

        return $activity;
    }

    // =========================================================================
    // EMAIL
    // =========================================================================

    protected function sendEmail(array $params, Lead $lead): bool
    {
        if (! class_exists(Email::class) || ! $lead->person) {
            return false;
        }

        $subject = $params['subject'] ?? "Follow-up: {$lead->title}";
        $body = $params['body'] ?? "Dear {$lead->person->name},\n\nWe'd like to follow up regarding {$lead->title}.";
        $body = $this->replacePlaceholders($body, $lead);

        Email::create([
            'subject' => $subject,
            'body' => $body,
            'to' => $lead->person->emails->first()?->value,
            'lead_id' => $lead->id,
            'person_id' => $lead->person_id,
            'user_id' => $lead->user_id,
        ]);

        return true;
    }

    protected function replacePlaceholders(string $text, Lead $lead): string
    {
        $replacements = [
            '{{lead_title}}' => $lead->title ?? '',
            '{{person_name}}' => $lead->person?->name ?? '',
            '{{agent_name}}' => $lead->user?->name ?? '',
            '{{company_name}}' => $lead->person?->organization?->name ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    // =========================================================================
    // WEBHOOK
    // =========================================================================

    protected function triggerWebhook(array $params, Lead $lead): bool
    {
        $url = $params['url'] ?? null;
        if (! $url) {
            return false;
        }

        $method = $params['method'] ?? 'POST';
        $headers = $params['headers'] ?? ['Content-Type' => 'application/json'];

        $payload = [
            'event' => 'lead.temperature_changed',
            'lead_id' => $lead->id,
            'lead_title' => $lead->title,
            'timestamp' => now()->toIso8601String(),
            'data' => $params['payload'] ?? [],
        ];

        try {
            Http::withHeaders($headers)
                ->{strtolower($method)}($url, $payload);

            return true;
        } catch (\Exception $e) {
            Log::error('Lead webhook failed: '.$e->getMessage());

            return false;
        }
    }
}
