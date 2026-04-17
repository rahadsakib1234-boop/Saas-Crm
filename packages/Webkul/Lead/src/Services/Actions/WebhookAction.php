<?php

namespace Webkul\Lead\Services\Actions;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Services\LeadActionInterface;

/**
 * Webhook Action
 *
 * Sends a webhook POST request to an external URL.
 */
class WebhookAction implements LeadActionInterface
{
    public function execute(array $params, $context): mixed
    {
        $url = $params['url'] ?? null;
        $method = strtoupper($params['method'] ?? 'POST');
        $headers = $params['headers'] ?? [];
        $payload = $params['payload'] ?? [];

        if (! $url) {
            throw new \InvalidArgumentException('WebhookAction requires "url" parameter');
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid webhook URL: '.$url);
        }

        $data = is_array($payload)
            ? $this->interpolateRecursive($payload, $context)
            : $this->interpolate($payload, $context);

        $requestHeaders = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);

        try {
            $response = Http::withHeaders($requestHeaders)
                ->timeout(10)
                ->send($method, $url, ['json' => $data]);

            $result = [
                'status_code' => $response->status(),
                'body' => $response->body(),
                'success' => $response->successful(),
            ];

            if (! $response->successful()) {
                Log::warning('WebhookAction failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('WebhookAction exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function name(): string
    {
        return 'webhook';
    }

    public function validate(array $params): bool
    {
        return isset($params['url']) && filter_var($params['url'], FILTER_VALIDATE_URL);
    }

    public function requiredParams(): array
    {
        return ['url'];
    }

    protected function interpolate(string $template, $context): string
    {
        if (! $context instanceof Lead) {
            return $template;
        }

        $replacements = [
            '{lead_id}' => $context->id,
            '{lead_title}' => $context->title,
            '{lead_value}' => $context->lead_value ?? '',
            '{person_id}' => $context->person_id ?? '',
            '{person_name}' => $context->person?->name ?? '',
            '{user_id}' => $context->user_id ?? '',
            '{status}' => $context->status ?? '',
            '{created_at}' => $context->created_at?->toIso8601String() ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function interpolateRecursive(array $data, $context): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $this->interpolate($value, $context);
            } elseif (is_array($value)) {
                $result[$key] = $this->interpolateRecursive($value, $context);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
