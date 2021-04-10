<?php

namespace Rokde\SubscriptionManager;

use Illuminate\Routing\Router;
use Rokde\SubscriptionManager\Commands\FeaturesListCommand;
use Rokde\SubscriptionManager\Commands\PlansListCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-subscription-manager')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_features_table')
            ->hasMigration('create_plans_table')
            ->hasMigration('create_plan_feature_table')
            ->hasMigration('create_subscriptions_table')
            ->hasCommand(FeaturesListCommand::class)
            ->hasCommand(PlansListCommand::class);
    }

    public function packageBooted()
    {
        parent::packageBooted();

        $this->registerRouteMiddleware();
    }

    private function registerRouteMiddleware(): void
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');
        $middleware = $config->get('subscription-manager::middleware');
        if (! $middleware) {
            return;
        }

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('subscribed', $middleware);
    }
}
