<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Rokde\SubscriptionManager\SubscribableResolver;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscribableResolverTest extends TestCase
{
    /** @test */
    public function it_can_resolve_the_current_authenticated_user()
    {
        SubscribableResolver::resolveSubscribable();

        $this->assertEquals(Auth::user(), SubscribableResolver::subscribable());
        $this->assertEquals(auth()->user(), SubscribableResolver::subscribable());
        $this->assertNull(SubscribableResolver::subscribable());
    }

    /** @test */
    public function it_can_resolve_another_subscribable()
    {
        $testSubscriber = new class {};

        SubscribableResolver::resolveSubscribable(function () use ($testSubscriber) {
            return $testSubscriber;
        });

        $this->assertEquals($testSubscriber, SubscribableResolver::subscribable());
    }
}
