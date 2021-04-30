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
        /** @var Subscription $subscription */
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
        $this->assertFalse($subscription->hasPlan());
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
        $this->assertFalse($subscription->hasPlan());
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
        $this->assertFalse($subscription->hasPlan());
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
        $this->assertFalse($subscription->hasPlan());
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
        $this->assertFalse($subscription->hasPlan());
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
        $this->assertTrue($subscription->hasPlan());
        $this->assertTrue($subscription->hasPlan($planA));
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

    /** @test */
    public function it_can_build_a_query_for_cancelled_subscriptions()
    {
        $query = Subscription::query()
            ->cancelled();

        $this->assertEquals('select * from "subscriptions" where "ends_at" is not null and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_not_cancelled_subscriptions()
    {
        $query = Subscription::query()
            ->notCancelled();

        $this->assertEquals('select * from "subscriptions" where "ends_at" is null and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_subscriptions_on_trial()
    {
        $query = Subscription::query()
            ->onTrial();

        $this->assertEquals('select * from "subscriptions" where "trial_ends_at" is not null and "trial_ends_at" > ? and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_subscriptions_not_on_trial()
    {
        $query = Subscription::query()
            ->notOnTrial();

        $this->assertEquals('select * from "subscriptions" where ("trial_ends_at" is null or "trial_ends_at" <= ?) and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_subscriptions_on_grace_period()
    {
        $query = Subscription::query()
            ->onGracePeriod();

        $this->assertEquals('select * from "subscriptions" where "ends_at" is not null and "ends_at" > ? and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_subscriptions_not_on_grace_period()
    {
        $query = Subscription::query()
            ->notOnGracePeriod();

        $this->assertEquals('select * from "subscriptions" where ("ends_at" is null or "ends_at" <= ?) and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_recurring_subscriptions()
    {
        $query = Subscription::query()
            ->recurring();

        $this->assertEquals('select * from "subscriptions" where ("trial_ends_at" is null or "trial_ends_at" <= ?) and "ends_at" is null and "period" is not null and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_can_build_a_query_for_ended_subscriptions()
    {
        $query = Subscription::query()
            ->ended();

        $this->assertEquals('select * from "subscriptions" where "ends_at" is not null and ("ends_at" is null or "ends_at" <= ?) and "subscriptions"."deleted_at" is null',
            $query->toSql());
    }

    /** @test */
    public function it_gets_an_uuid_automatically()
    {
        $subscription = new Subscription();
        $subscription->subscribable_type = 'Test';
        $subscription->subscribable_id = 1;

        $subscription->save();

        $this->assertNotNull($subscription->uuid);
    }
}
