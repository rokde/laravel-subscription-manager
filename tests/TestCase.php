<?php

namespace Rokde\SubscriptionManager\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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

        Schema::create('test_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        include_once __DIR__.'/../database/migrations/create_features_table.php.stub';
        (new \CreateFeaturesTable())->up();

        include_once __DIR__.'/../database/migrations/create_plans_table.php.stub';
        (new \CreatePlansTable())->up();

        include_once __DIR__.'/../database/migrations/create_plan_feature_table.php.stub';
        (new \CreatePlanFeatureTable())->up();

        include_once __DIR__.'/../database/migrations/create_subscriptions_table.php.stub';
        (new \CreateSubscriptionsTable())->up();

        include_once __DIR__.'/../database/migrations/create_subscription_features_table.php.stub';
        (new \CreateSubscriptionFeaturesTable())->up();

        include_once __DIR__.'/../database/migrations/create_subscription_feature_usages_table.php.stub';
        (new \CreateSubscriptionFeatureUsagesTable())->up();
    }
}
