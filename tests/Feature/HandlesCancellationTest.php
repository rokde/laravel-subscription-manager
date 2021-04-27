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

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isEnded());
    }

    /** @test */
    public function it_can_not_cancel_a_subscription_twice()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => now()->addMonth(),
        ]);

        $subscription->cancel();

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isEnded());
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

        $this->assertFalse($subscription->isEnded());
        $this->assertTrue($subscription->isOnGracePeriod());
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

        $this->assertFalse($subscription->isActive());
        $this->assertFalse($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGracePeriod());
        $this->assertTrue($subscription->isEnded());
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

        $this->assertFalse($subscription->isActive());
        $this->assertFalse($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGracePeriod());
        $this->assertTrue($subscription->isEnded());
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

        $this->assertFalse($subscription->isEnded());
        $this->assertTrue($subscription->isOnGracePeriod());
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

        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isOnGracePeriod());

        $subscription->resume();

        $this->assertFalse($subscription->isCancelled());
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

        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGracePeriod());

        $subscription->resume();

        $this->assertTrue($subscription->isCancelled());
    }

    /** @test */
    public function it_can_use_ends_at_for_next_period()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $cancelAt = now()->subMinute();
        $subscription->cancelAt($cancelAt);

        $nextPeriod = $subscription->nextPeriod();
        $this->assertTrue($cancelAt->addYear()->isSameDay($nextPeriod));
    }
}
