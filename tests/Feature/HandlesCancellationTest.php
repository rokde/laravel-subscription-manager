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
    public function it_can_cancel_on_the_end_of_the_trial_when_you_are_on_a_trial()
    {
        $endsAt = now()->addDays(30);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => $endsAt,
            'ends_at' => null,
        ]);

        $subscription->cancel();

        $this->assertFalse($subscription->ended());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertEquals($endsAt->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        $this->assertFalse($subscription->isInfinite());
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

    /** @test */
    public function it_can_cancel_an_infinite_subscription_immediately()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => null,
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $subscription->cancel();

        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertTrue($subscription->ended());
        $this->assertTrue($subscription->isInfinite());
    }

    /** @test */
    public function it_can_cancel_on_the_end_of_the_trial_when_you_are_on_a_trial_on_infinite_subscriptions_too()
    {
        $endsAt = now()->addDays(30);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => null,
            'trial_ends_at' => $endsAt,
            'ends_at' => null,
        ]);

        $subscription->cancel();

        $this->assertFalse($subscription->ended());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertEquals($endsAt->toDateTimeString(), $subscription->ends_at->toDateTimeString());
        $this->assertTrue($subscription->isInfinite());
    }

    /** @test */
    public function it_can_resume_a_cancelled_subscription()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $subscription->cancel();

        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->onGracePeriod());

        $subscription->resume();

        $this->assertFalse($subscription->cancelled());
    }

    /** @test */
    public function it_can_resume_a_cancelled_subscription_just_within_grace_period()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $subscription->cancelAt(now()->subMinute());

        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());

        $subscription->resume();

        $this->assertTrue($subscription->cancelled());
    }
}
