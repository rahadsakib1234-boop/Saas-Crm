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
 * Each action type has its own handler method.
 *
 * Supported actions:
 *   - add_tag        → Attach tag to lead
 *   - remove_tag     → Remove tag from lead
 *   - update_field   → Update lead field
 *   - notify_agent   → Send in-app notification
 *   - create_task    → Create follow-up task
 *   - schedule_activity → Schedule call/meeting
 *   - send_email     → Send templated email
 *   - webhook        → POST to external URL
 */
class LeadActionExecutor
{
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
     * Execute multiple actions from a threshold.
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

            $results[] = [
                'action' => $actionType,
                'result' => $this->execute($actionType, $params, $lead),
            ];
        }

        return $results;
    }

    // ========== TAG ACTIONS ==========

    /**
     * Add a tag to the lead.
     */
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

    /**
     * Remove a tag from the lead.
     */
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

    // ========== FIELD UPDATE ACTIONS ==========

    /**
     * Update a field on the lead.
     */
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

    // ========== NOTIFICATION ACTIONS ==========

    /**
     * Send notification to the assigned agent.
     */
    protected function notifyAgent(array $params, Lead $lead): bool
    {
        $title = $params['title'] ?? 'Lead Update';
        $body = $params['body'] ?? 'A lead requires your attention';
        $priority = $params['priority'] ?? 'medium';

        // Only notify if lead has an assigned user
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

    // ========== TASK ACTIONS ==========

    /**
     * Create a follow-up task for the lead.
     */
    protected function createTask(array $params, Lead $lead): ?object
    {
        $title = $params['title'] ?? 'Follow up required';
        $description = $params['description'] ?? null;
        $dueDays = $params['due_days'] ?? 1;
        $priority = $params['priority'] ?? 'medium';

        // Use Activity system if available, otherwise create basic task
        if (class_exists('\Webkul\Activity\Models\Activity')) {
            $dueDate = Carbon::now()->addDays($dueDays)->toDateString();

            return Activity::create([
                'title' => $title,
                'description' => $description,
                'type' => 'task',
                'status' => 'pending',
                'user_id' => $lead->user_id,
                'lead_id' => $lead->id,
                'schedule_from' => Carbon::now()->toDateString(),
                'schedule_to' => $dueDate,
                'priority' => $priority,
            ]);
        }

        return null;
    }

    // ========== SCHEDULE ACTIVITY ACTIONS ==========

    /**
     * Schedule a follow-up activity (call/meeting).
     */
    protected function scheduleActivity(array $params, Lead $lead): ?object
    {
        $title = $params['title'] ?? 'Scheduled Follow-up';
        $type = $params['type'] ?? 'call';  // call, meeting, email
        $description = $params['description'] ?? null;
        $daysAhead = $params['days_ahead'] ?? 1;

        if (! class_exists('\Webkul\Activity\Models\Activity')) {
            return null;
        }

        $scheduledAt = Carbon::now()->addDays($daysAhead);

        return Activity::create([
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'status' => 'pending',
            'user_id' => $lead->user_id,
            'lead_id' => $lead->id,
            'schedule_from' => $scheduledAt->toDateString(),
            'schedule_to' => $scheduledAt->addHours(1)->toDateString(),
        ]);
    }

    // ========== EMAIL ACTIONS ==========

    /**
     * Send templated email to lead contact.
     */
    protected function sendEmail(array $params, Lead $lead): bool
    {
        // Check if email system exists
        if (! class_exists('\Webkul\Email\Models\Email') || ! $lead->person) {
            return false;
        }

        $subject = $params['subject'] ?? "Follow-up: {$lead->title}";
        $body = $params['body'] ?? "Dear {$lead->person->name},\n\nWe'd like to follow up regarding {$lead->title}.";

        // Replace placeholders
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

    /**
     * Replace placeholders in text with lead data.
     */
    protected function replacePlaceholders(string $text, Lead $lead): string
    {
        $replacements = [
            '{{lead_title}}' => $lead->title ?? '',
            '{{person_name}}' => $lead->person?->name ?? '',
            '{{agent_name}}' => $lead->user?->name ?? '',
            '{{company_name}}' => $lead->person?->organization?->name ?? '',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $text
        );
    }

    // ========== WEBHOOK ACTIONS ==========

    /**
     * Trigger external webhook.
     */
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
