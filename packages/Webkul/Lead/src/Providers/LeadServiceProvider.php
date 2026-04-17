<?php

namespace Webkul\Lead\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Observers\LeadObserver;
use Webkul\Lead\Services\AutomationGuard;
use Webkul\Lead\Services\LeadActionExecutor;
use Webkul\Lead\Services\LeadActionRegistry;
use Webkul\Lead\Services\LeadAutomationRuleEngine;

/**
 * Lead Service Provider
 *
 * Bootstraps the Leads module including:
 *   - Observer registration
 *   - Action registry initialization
 *   - Service bindings
 */
class LeadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Boot the action registry with default actions
        LeadActionRegistry::boot();

        // Register the Lead observer
        Lead::observe(LeadObserver::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register AutomationGuard as singleton (persists across requests)
        $this->app->singleton(AutomationGuard::class, function () {
            return new AutomationGuard;
        });

        // Register LeadActionExecutor
        $this->app->singleton(LeadActionExecutor::class, function ($app) {
            return new LeadActionExecutor(
                $app->make(AutomationGuard::class)
            );
        });

        // Register LeadAutomationRuleEngine
        $this->app->singleton(LeadAutomationRuleEngine::class, function ($app) {
            return new LeadAutomationRuleEngine(
                $app->make(AutomationGuard::class)
            );
        });
    }
}
