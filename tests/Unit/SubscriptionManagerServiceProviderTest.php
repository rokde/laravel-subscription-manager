<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Unit;

use Illuminate\Routing\Router;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscriptionManagerServiceProviderTest extends TestCase
{
    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('subscription-manager.middleware', null);
    }

    /** @test */
    public function it_succeeds_when_no_subscribed_middleware_set()
    {
        $router = $this->app->make(Router::class);
        $this->assertEmpty($router->getMiddleware());
    }
}
