<?php

namespace Rokde\SubscriptionManager;

use Rokde\SubscriptionManager\Commands\FeaturesListCommand;
use Rokde\SubscriptionManager\Commands\PlansListCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SubscriptionManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-subscription-manager')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('2021_04_05_000000_create_features_table')
            ->hasMigration('2021_04_05_000001_create_plans_table')
            ->hasMigration('2021_04_05_000002_create_plan_features_table')
            ->hasCommand(FeaturesListCommand::class)
            ->hasCommand(PlansListCommand::class);
    }
}
