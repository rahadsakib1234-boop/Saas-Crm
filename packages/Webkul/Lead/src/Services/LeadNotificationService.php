<?php

namespace Webkul\Lead\Services;

use Illuminate\Support\Facades\Log;
use Webkul\Lead\Models\Lead;
use Webkul\Notification\Models\Notification;

/**
 * Lead Notification Service
 *
 * Handles in-app notifications for lead events.
 * Currently uses a simple database-based notification system.
 * Can be extended to use Laravel's built-in notification system,
 * or integrated with external services (email, SMS, Slack, etc.)
 */
class LeadNotificationService
{
    /**
     * Send a notification to a user about a lead.
     *
     * @param  string  $priority  'low'|'medium'|'high'
     */
    public function send(int $userId, string $title, string $body, string $priority = 'medium', ?Lead $lead = null): bool
    {
        // Skip if notifications disabled
        if (! config('lead_temperature.notify_agent.enabled', true)) {
            return false;
        }

        // Skip if no user to notify
        if (! $userId) {
            return false;
        }

        try {
            $this->createNotification([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'priority' => $priority,
                'lead_id' => $lead?->id,
                'type' => 'lead_temperature_alert',
            ]);

            Log::info("Lead notification sent to user {$userId}: {$title}");

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send lead notification: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Create a notification record.
     */
    protected function createNotification(array $data): mixed
    {
        // Check if Krayin has a notification system we can use
        if (class_exists('\Webkul\Notification\Models\Notification')) {
            return Notification::create([
                'type' => $data['type'] ?? 'general',
                'title' => $data['title'] ?? 'Notification',
                'body' => $data['body'] ?? '',
                'user_id' => $data['user_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
                'read_at' => null,
            ]);
        }

        // Fallback: log notification if no notification model exists
        Log::info("Lead notification (fallback): user={$data['user_id']}, title={$data['title']}");

        return true;
    }

    /**
     * Send hot lead notification to agent.
     */
    public function sendHotLeadAlert(Lead $lead): bool
    {
        if (! $lead->user_id) {
            return false;
        }

        return $this->send(
            $lead->user_id,
            '🔥 Hot Lead Alert',
            "Urgent lead '{$lead->title}' requires immediate attention",
            'high',
            $lead
        );
    }

    /**
     * Send lead temperature change notification.
     */
    public function sendTemperatureChangeAlert(Lead $lead, string $fromTemp, string $toTemp): bool
    {
        if (! $lead->user_id) {
            return false;
        }

        return $this->send(
            $lead->user_id,
            "Lead temperature changed to {$toTemp}",
            "Lead '{$lead->title}' changed from {$fromTemp} to {$toTemp}",
            'medium',
            $lead
        );
    }
}
