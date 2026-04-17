<?php

namespace Webkul\Lead\Services\Actions;

use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;
use Webkul\Lead\Services\LeadNotificationService;

/**
 * Notify User Action
 *
 * Sends an in-app notification to the lead's owner.
 */
class NotifyUserAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        if (! $context instanceof Lead) {
            throw new \InvalidArgumentException('NotifyUserAction requires Lead context');
        }

        $title    = $params['title'] ?? 'Lead Updated';
        $body     = $params['body'] ?? 'A lead has been automatically processed.';
        $priority = $params['priority'] ?? 'normal';

        $body = $this->interpolate($body, $context);

        $notificationService = new LeadNotificationService();

        // FIX: LeadNotificationService exposes send(), not notify()
        $notificationService->send(
            $context->user_id,
            $title,
            $body,
            $priority,
            $context
        );

        return [
            'notified_user_id' => $context->user_id,
            'title'            => $title,
            'priority'         => $priority,
        ];
    }

    public function name(): string
    {
        return 'notify_user';
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
            '{lead_title}'  => $lead->title,
            '{lead_id}'     => $lead->id,
            '{lead_value}'  => $lead->lead_value ?? 'N/A',
            '{person_name}' => $lead->person?->name ?? 'Unknown',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
