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
use Webkul\Lead\Services\LeadNotificationService;
use Webkul\Lead\Services\LeadTemperatureClassifier;

/**
 * Lead Service Provider
 *
 * Bootstraps the Leads module including:
 *   - Observer registration
 *   - Action registry initialization
 *   - Service container bindings
 */
class LeadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        LeadActionRegistry::boot();

        Lead::observe(LeadObserver::class);
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // AutomationGuard — singleton so state is consistent within a request
        $this->app->singleton(AutomationGuard::class, function () {
            return new AutomationGuard();
        });

        // LeadNotificationService
        $this->app->singleton(LeadNotificationService::class, function () {
            return new LeadNotificationService();
        });

        // FIX: LeadActionExecutor needs LeadNotificationService, NOT AutomationGuard
        $this->app->singleton(LeadActionExecutor::class, function ($app) {
            return new LeadActionExecutor(
                $app->make(LeadNotificationService::class)
            );
        });

        // FIX: Register LeadTemperatureClassifier (was missing entirely)
        $this->app->singleton(LeadTemperatureClassifier::class, function ($app) {
            return new LeadTemperatureClassifier(
                $app->make(AutomationGuard::class),
                $app->make(LeadActionExecutor::class)
            );
        });

        // LeadAutomationRuleEngine — does not need AutomationGuard in its constructor
        $this->app->singleton(LeadAutomationRuleEngine::class, function ($app) {
            return new LeadAutomationRuleEngine();
        });
    }
}
