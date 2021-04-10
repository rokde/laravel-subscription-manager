<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Rokde\SubscriptionManager\Events\SubscriptionCanceled;
use Rokde\SubscriptionManager\Events\SubscriptionCreated;
use Rokde\SubscriptionManager\Events\SubscriptionResumed;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscriptionEventsTest extends TestCase
{
    /** @test */
    public function it_throws_events_on_creating_a_subscription()
    {
        Event::fake();

        $subscription = Subscription::factory()->create();

        Event::assertDispatched(SubscriptionCreated::class, function (SubscriptionCreated $event) use ($subscription) {
            return $event->subscription->is($subscription);
        });
    }

    /** @test */
    public function it_throws_events_on_cancelling_a_subscription()
    {
        $ran = false;

        Event::listen(SubscriptionCanceled::class, function (SubscriptionCanceled $event) use (&$ran) {
            $this->assertInstanceOf(Subscription::class, $event->subscription);
            $ran = true;
        });

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();
        $subscription->cancel();

        $this->assertTrue($ran);
    }

    /** @test */
    public function it_throws_events_on_resuming_a_subscription()
    {
        $ran = false;

        Event::listen(SubscriptionResumed::class, function (SubscriptionResumed $event) use (&$ran) {
            $this->assertInstanceOf(Subscription::class, $event->subscription);
            $ran = true;
        });

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();
        $subscription->cancel();
        $this->assertFalse($ran);

        $subscription->resume();
        $this->assertTrue($ran);
    }
}
