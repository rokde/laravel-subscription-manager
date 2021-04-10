<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Insights;

use Rokde\SubscriptionManager\Insights\Customer;
use Rokde\SubscriptionManager\Tests\TestCase;
use Rokde\SubscriptionManager\Tests\TestUser;

class CustomerTest extends TestCase
{
    /** @test */
    public function it_can_get_distinct_customers()
    {
        $testUser = new TestUser(['id' => 1]);
        $testUser->save();

        $testUser->newSubscription()->create();
        $testUser->newSubscription()->create();

        $testUser2 = new TestUser(['id' => 2]);
        $testUser2->save();

        $testUser2->newSubscription()->create();

        $this->assertDatabaseCount('subscriptions', 3);

        $customer = new Customer();

        $this->assertCount(2, $customer->get());
        $this->assertInstanceOf(TestUser::class, $customer->get()[0]);
        $this->assertInstanceOf(TestUser::class, $customer->get()[1]);
    }

    /** @test */
    public function it_can_get_customers_for_a_period()
    {
        $lastMonth = now()->subMonth()->clone();
        $now = now()->clone();
        $nextMonth = now()->addMonth()->clone();
        $inTwoMonths = now()->addMonths(2)->clone();

        $testUser = new TestUser(['id' => 1]);
        $testUser->save();

        ($testUser->newSubscription()->create())->update([
            'created_at' => $lastMonth->toDateTimeString(),
            'ends_at' => null,
        ]);

        $testUser2 = new TestUser(['id' => 2]);
        $testUser2->save();

        ($testUser2->newSubscription()->create())->update([
            'created_at' => $now->toDateTimeString(),
            'ends_at' => $now->clone()->addHour()->toDateTimeString(),
        ]);

        $testUser3 = new TestUser(['id' => 3]);
        $testUser3->save();

        ($testUser3->newSubscription()->create())->update([
            'created_at' => $nextMonth->toDateTimeString(),
            'ends_at' => null,
        ]);

        $this->assertDatabaseCount('subscriptions', 3);

        $customer = new Customer();

        $lastMonthPeriod = $lastMonth->toPeriod($now)->clone();

        $customersLastMonth = $customer->get($lastMonthPeriod);
        $this->assertCount(2, $customersLastMonth);
        $this->assertEquals(1, $customersLastMonth[0]->id);
        $this->assertEquals(2, $customersLastMonth[1]->id);

        $thisMonthPeriod = $now->toPeriod($nextMonth)->clone();

        $customersThisMonth = $customer->get($thisMonthPeriod);
        $this->assertCount(3, $customersThisMonth);
        $this->assertEquals(1, $customersThisMonth[0]->id);
        $this->assertEquals(2, $customersThisMonth[1]->id);
        $this->assertEquals(3, $customersThisMonth[2]->id);

        $nextMonthPeriod = $nextMonth->toPeriod($inTwoMonths)->clone();

        $customersNextMonth = $customer->get($nextMonthPeriod);
        $this->assertCount(2, $customersNextMonth);
        $this->assertEquals(1, $customersNextMonth[0]->id);
        $this->assertEquals(3, $customersNextMonth[1]->id);
    }

    /** @test */
    public function it_can_get_churn_customers_for_a_period()
    {
        $lastMonth = now()->subMonth()->clone();
        $now = now()->clone();
        $nextMonth = now()->addMonth()->clone();
        $inTwoMonths = now()->addMonths(2)->clone();

        $testUser = new TestUser(['id' => 1]);
        $testUser->save();

        $testUser->newSubscription()->create();

        $testUser2 = new TestUser(['id' => 2]);
        $testUser2->save();

        $subscription = $testUser2->newSubscription()->create();
        $subscription->update([
            'ends_at' => $now->clone()->addHour()->toDateTimeString(),
        ]);

        $this->assertDatabaseCount('subscriptions', 2);

        $customer = new Customer();

        $lastMonthPeriod = $lastMonth->toPeriod($now)->clone();

        $churnCustomersLastMonth = $customer->churnCustomers($lastMonthPeriod);
        $this->assertCount(0, $churnCustomersLastMonth);

        $thisMonthPeriod = $now->toPeriod($nextMonth)->clone();

        $churnCustomersThisMonth = $customer->churnCustomers($thisMonthPeriod);
        $this->assertCount(1, $churnCustomersThisMonth);
        $this->assertEquals(2, $churnCustomersThisMonth[0]->id);

        $nextMonthPeriod = $nextMonth->toPeriod($inTwoMonths)->clone();

        $churnCustomersNextMonth = $customer->churnCustomers($nextMonthPeriod);
        $this->assertCount(0, $churnCustomersNextMonth);
    }
}
