<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature;

use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;

class HandlesCancellationTest extends TestCase
{
    /** @test */
    public function it_can_cancel_a_subscription()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $subscription->cancel();

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function it_can_cancel_a_subscription_immediately()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $subscription->cancelNow();

        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertTrue($subscription->ended());
    }
}
