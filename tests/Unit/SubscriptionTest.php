<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /** @test */
    public function a_subscription_can_have_no_trial()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertTrue($subscription->isRecurring());
        $this->assertFalse($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isEnded());
    }

    /** @test */
    public function a_subscription_can_have_trial()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => Carbon::now()->addDay(),
            'ends_at' => null,
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isValid());
        $this->assertTrue($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertFalse($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isEnded());
    }

    /** @test */
    public function a_subscription_can_be_cancelled()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->addYear(),
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isEnded());
    }

    /** @test */
    public function a_subscription_can_be_cancelled_with_trial()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addYear(),
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isValid());
        $this->assertTrue($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertTrue($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isEnded());
    }

    /** @test */
    public function a_subscription_can_be_ended()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($subscription->isActive());
        $this->assertFalse($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->isOnGracePeriod());
        $this->assertTrue($subscription->isEnded());
    }

    /** @test */
    public function a_subscription_can_link_to_a_plan_and_vice_versa()
    {
        /** @var Plan $planA */
        $planA = Plan::factory()->create(['name' => 'a']);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'plan_id' => $planA->getKey(),
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertInstanceOf(Plan::class, $subscription->plan);
        $this->assertEquals($planA->getKey(), $subscription->plan->getKey());
        $this->assertInstanceOf(Subscription::class, $planA->subscriptions->first());
        $this->assertEquals($planA->subscriptions->first()->getKey(), $subscription->getKey());
    }

    /** @test */
    public function a_test_has_one_year_as_default_period_length()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertEquals(CarbonInterval::year(1), $subscription->periodLength());
    }

    /** @test */
    public function a_subscription_can_be_infinite()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => null,
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($subscription->isRecurring());
        $this->assertEquals(CarbonInterval::years(1000), $subscription->periodLength());
    }
}
