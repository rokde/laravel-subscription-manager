<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Unit;

use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Models\SubscriptionCircle;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscriptionCircleTest extends TestCase
{
    /** @test */
    public function it_can_have_a_circle()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $circles = $subscription->circles();
        $circle = $circles[0];

        $this->assertCount(1, $circles);
        $this->assertInstanceOf(SubscriptionCircle::class, $circle);
        $this->assertEquals($subscription, $circle->subscription());
        $this->assertEquals('P1Y', $circle->intervalString());
        $this->assertTrue(now()->isSameDay($circle->start()));
        $this->assertTrue(now()->addYear()->isSameDay($circle->end()));
    }

    /** @test */
    public function it_can_have_circles()
    {
        $createdAt = now()->addSeconds(2)->subYear();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => 'P1M',
            'trial_ends_at' => null,
            'ends_at' => null,
            'created_at' => $createdAt,
        ]);

        $circles = $subscription->circles();
        $circle = $circles[0];

        $this->assertCount(12, $circles);
        $this->assertEquals($subscription, $circle->subscription());
        $this->assertInstanceOf(SubscriptionCircle::class, $circle);
        $this->assertEquals('P1M', $circle->intervalString());
        $this->assertEquals('10', $circles[9]->number());

        $this->assertEquals([
            'id' => '1-6',
            'subscription_id' => $subscription->id,
            'number' => 6,
            'start' => $createdAt->addMonths(5)->toDateTimeString(),
            'end' => $createdAt->addMonth()->toDateTimeString(),
            'interval' => 'P1M',
        ], $circles[5]->toArray());
    }

    /** @test */
    public function it_has_one_circle_for_infinite_subscriptions()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => null,
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);

        $circles = $subscription->circles();
        $circle = $circles[0];

        $this->assertCount(1, $circles);
        $this->assertEquals('P1000Y', $circle->intervalString());
    }

    /** @test */
    public function it_can_have_circles_until_the_end_date_when_cancelled()
    {
        $createdAt = now()->addSeconds(2)->subYear();
        $cancelAt = now()->subMinutes(25)->subMonths(3);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => 'P1M',
            'trial_ends_at' => null,
            'ends_at' => null,
            'created_at' => $createdAt,
        ]);
        $subscription->cancelAt($cancelAt);

        $circles = $subscription->circles();
        $circle = $circles[8];

        $this->assertCount(9, $circles);
        $this->assertInstanceOf(SubscriptionCircle::class, $circle);
        $this->assertEquals('P1M', $circles[6]->intervalString());
        $this->assertEquals('P1M', $circles[7]->intervalString());
        $this->assertEquals('P30DT23H34M58S', $circle->intervalString());
        $this->assertEquals('9', $circle->number());

        $this->assertEquals([
            'id' => '1-6',
            'subscription_id' => $subscription->id,
            'number' => 6,
            'start' => $createdAt->clone()->addMonths(5)->toDateTimeString(),
            'end' => $createdAt->clone()->addMonths(6)->toDateTimeString(),
            'interval' => 'P1M',
        ], $circles[5]->toArray());

        $this->assertEquals([
            'id' => '1-9',
            'subscription_id' => $subscription->id,
            'number' => 9,
            'start' => $createdAt->clone()->addMonths(8)->toDateTimeString(),
            'end' => $cancelAt->toDateTimeString(),
            'interval' => 'P30DT23H34M58S',
        ], $circle->toArray());
    }

    /** @test */
    public function it_can_have_circles_until_the_end_date_when_cancelled_in_future()
    {
        $createdAt = now()->startOfMonth()->addSeconds(2)->subYear();
        $cancelAt = now()->startOfMonth()->addMonths(3)->addHours(3);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'period' => 'P1M',
            'trial_ends_at' => null,
            'ends_at' => null,
            'created_at' => $createdAt,
        ]);
        $subscription->cancelAt($cancelAt);

        $circles = $subscription->circles();
        $circle = $circles[15];

        $this->assertCount(16, $circles);
        $this->assertInstanceOf(SubscriptionCircle::class, $circle);
        $this->assertEquals('P1M', $circles[6]->intervalString());
        $this->assertEquals('P1M', $circles[7]->intervalString());
        $this->assertEquals('PT2H59M58S', $circle->intervalString());
        $this->assertEquals(16, $circle->number());

        $this->assertEquals([
            'id' => '1-6',
            'subscription_id' => $subscription->id,
            'number' => 6,
            'start' => $createdAt->clone()->addMonths(5)->toDateTimeString(),
            'end' => $createdAt->clone()->addMonths(6)->toDateTimeString(),
            'interval' => 'P1M',
        ], $circles[5]->toArray());

        $this->assertEquals([
            'id' => '1-16',
            'subscription_id' => $subscription->id,
            'number' => 16,
            'start' => $createdAt->clone()->addMonths(15)->toDateTimeString(),
            'end' => $cancelAt->toDateTimeString(),
            'interval' => 'PT2H59M58S',
        ], $circle->toArray());
    }
}
