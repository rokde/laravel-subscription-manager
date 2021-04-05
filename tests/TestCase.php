<?php

namespace Rokde\SubscriptionManager\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Rokde\SubscriptionManager\SubscriptionManagerServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Rokde\\SubscriptionManager\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SubscriptionManagerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/../database/migrations/create_features_table.php.stub';
        (new \CreateFeaturesTable())->up();

        include_once __DIR__.'/../database/migrations/create_plans_table.php.stub';
        (new \CreatePlansTable())->up();

        include_once __DIR__.'/../database/migrations/create_plan_feature_table.php.stub';
        (new \CreatePlanFeatureTable())->up();
    }
}
