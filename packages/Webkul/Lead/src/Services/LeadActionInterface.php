<?php

namespace Webkul\Lead\Services;

/**
 * Lead Action Interface
 *
 * All automation actions must implement this interface.
 * Each action is self-contained and testable in isolation.
 *
 * @see LeadActionRegistry
 */
interface LeadActionInterface
{
    /**
     * Execute the action.
     *
     * @param  array  $params  Action parameters from config/rule
     * @param  mixed  $context  The triggering entity (usually Lead)
     * @return mixed  Result of execution (varies by action)
     */
    public function execute(array $params, $context): mixed;

    /**
     * Get the name of this action.
     */
    public function name(): string;

    /**
     * Validate action parameters.
     *
     * @param  array  $params
     * @return bool
     */
    public function validate(array $params): bool;

    /**
     * Get required parameters for this action.
     *
     * @return array<string>
     */
    public function requiredParams(): array;
}