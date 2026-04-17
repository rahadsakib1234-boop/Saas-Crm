<?php

namespace Webkul\Lead\Services;

use Webkul\Lead\Services\Actions\AddTagAction;
use Webkul\Lead\Services\Actions\CreateTaskAction;
use Webkul\Lead\Services\Actions\MoveToStageAction;
use Webkul\Lead\Services\Actions\NotifyUserAction;
use Webkul\Lead\Services\Actions\RemoveTagAction;
use Webkul\Lead\Services\Actions\WebhookAction;

/**
 * Lead Action Registry
 *
 * Central registry for all automation actions.
 * Each action is a self-contained class implementing LeadActionInterface.
 *
 * Benefits:
 *   - Actions are plugins, not hardcoded
 *   - Easy to add new actions without modifying existing code
 *   - Each action is testable in isolation
 *   - Actions can have their own config, validation, dependencies
 *
 * Usage:
 *   LeadActionRegistry::register('add_tag', AddTagAction::class);
 *   LeadActionRegistry::execute('add_tag', $params, $lead);
 *
 * @see LeadActionInterface
 */
class LeadActionRegistry
{
    /**
     * Registered actions.
     *
     * @var array<string, class-string<LeadActionInterface>>
     */
    protected static array $actions = [];

    /**
     * Default actions (auto-registered).
     *
     * @var array<string, class-string<LeadActionInterface>>
     */
    protected static array $defaultActions = [
        'add_tag' => AddTagAction::class,
        'remove_tag' => RemoveTagAction::class,
        'notify_user' => NotifyUserAction::class,
        'create_task' => CreateTaskAction::class,
        'move_to_stage' => MoveToStageAction::class,
        'webhook' => WebhookAction::class,
    ];

    /**
     * Initialize registry with default actions.
     */
    public static function boot(): void
    {
        foreach (self::$defaultActions as $name => $class) {
            self::register($name, $class);
        }
    }

    /**
     * Register an action.
     *
     * @param  class-string<LeadActionInterface>  $class
     */
    public static function register(string $name, string $class): void
    {
        if (! is_subclass_of($class, LeadActionInterface::class)) {
            throw new \InvalidArgumentException(
                "Action class {$class} must implement ".LeadActionInterface::class
            );
        }

        self::$actions[$name] = $class;
    }

    /**
     * Unregister an action.
     */
    public static function unregister(string $name): void
    {
        unset(self::$actions[$name]);
    }

    /**
     * Get all registered action names.
     *
     * @return array<string>
     */
    public static function getRegisteredActions(): array
    {
        return array_keys(self::$actions);
    }

    /**
     * Check if an action is registered.
     */
    public static function has(string $name): bool
    {
        return isset(self::$actions[$name]);
    }

    /**
     * Get action class by name.
     */
    public static function get(string $name): ?string
    {
        return self::$actions[$name] ?? null;
    }

    /**
     * Execute an action by name.
     *
     * @param  mixed  $context  Usually Lead model
     * @return array ['success' => bool, 'result' => mixed, 'error' => string|null]
     */
    public static function execute(string $name, array $params, $context): array
    {
        if (! self::has($name)) {
            return [
                'success' => false,
                'error' => "Unknown action: {$name}",
                'result' => null,
            ];
        }

        $class = self::$actions[$name];
        $action = new $class;

        try {
            $result = $action->execute($params, $context);

            return [
                'success' => true,
                'result' => $result,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null,
            ];
        }
    }
}
