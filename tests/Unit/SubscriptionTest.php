<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Unit;

use Carbon\Carbon;
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

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertTrue($subscription->recurring());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function a_subscription_can_have_trial()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => Carbon::now()->addDay(),
            'ends_at' => null,
        ]);

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertFalse($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function a_subscription_can_be_cancelled()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->addYear(),
        ]);

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function a_subscription_can_be_cancelled_with_trial()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => Carbon::now()->addDay(),
            'ends_at' => Carbon::now()->addYear(),
        ]);

        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->valid());
        $this->assertTrue($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->cancelled());
        $this->assertTrue($subscription->onGracePeriod());
        $this->assertFalse($subscription->ended());
    }

    /** @test */
    public function a_subscription_can_be_ended()
    {
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertFalse($subscription->active());
        $this->assertFalse($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->recurring());
        $this->assertTrue($subscription->cancelled());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertTrue($subscription->ended());
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
}
