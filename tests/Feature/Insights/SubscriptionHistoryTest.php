<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Insights;

use Carbon\Carbon;
use Rokde\SubscriptionManager\Insights\SubscriptionHistory;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;
use Rokde\SubscriptionManager\Tests\TestUser;

class SubscriptionHistoryTest extends TestCase
{
    /** @test */
    public function it_can_display_historical_data_for_subscriptions_partitioned_by_week()
    {
        $testUser = new TestUser(['id' => 1]);
        $testUser->save();
        $subscription1 = $testUser->newSubscription()->create();
        $subscription1->update(['created_at' => '2021-01-01 09:00:00']);

        $subscription2 = $testUser->newSubscription()->create();
        $subscription2->update(['created_at' => '2021-01-10 09:00:00']);

        $subscription1->cancelAt(Carbon::parse('2021-01-20'));

        $history = (new SubscriptionHistory())
            ->from(Carbon::parse('2021-01-01 00:00:00'))
            ->until(Carbon::parse('2021-02-01 00:00:00'))
            ->groupBy(SubscriptionHistory::PERIOD_WEEK);

        $histogram = $history->get();

        $this->assertEquals([
            'start' => '2020-12-28 00:00:00',
            'end' => '2021-01-04 00:00:00',
            'count' => 1,
            'new' => 1,
            'trial' => 0,
            'grace' => 1,
            'ended' => 0,
        ], $histogram->get('202053'));
        $this->assertEquals([
            'start' => '2021-01-04 00:00:00',
            'end' => '2021-01-11 00:00:00',
            'count' => 2,
            'new' => 1,
            'trial' => 0,
            'grace' => 1,
            'ended' => 0,
        ], $histogram->get('202101'));
        $this->assertEquals([
            'start' => '2021-01-11 00:00:00',
            'end' => '2021-01-18 00:00:00',
            'count' => 2,
            'new' => 0,
            'trial' => 0,
            'grace' => 1,
            'ended' => 0,
        ], $histogram->get('202102'));
        $this->assertEquals([
            'start' => '2021-01-18 00:00:00',
            'end' => '2021-01-25 00:00:00',
            'count' => 2,
            'new' => 0,
            'trial' => 0,
            'grace' => 1,
            'ended' => 1,
        ], $histogram->get('202103'));
        $this->assertEquals([
            'start' => '2021-01-25 00:00:00',
            'end' => '2021-02-01 00:00:00',
            'count' => 1,
            'new' => 0,
            'trial' => 0,
            'grace' => 0,
            'ended' => 0,
        ], $histogram->get('202104'));
    }

    /** @test */
    public function it_can_display_historical_data_for_subscriptions_partitioned_by_day()
    {
        $testUser = new TestUser(['id' => 1]);
        $testUser->save();
        $subscription1 = $testUser->newSubscription()->trialDays(3)->create();
        $subscription1->update(['created_at' => '2021-01-01 09:00:00']);
        $subscription1->update(['trial_ends_at' => '2021-01-04 09:00:00']);

        $subscription2 = $testUser->newSubscription()->create();
        $subscription2->update(['created_at' => '2021-01-04 09:00:00']);

        $subscription1->cancelAt(Carbon::parse('2021-01-05'));
        $subscription2->cancelAt(Carbon::parse('2021-01-05'));

        $history = (new SubscriptionHistory())
            ->from(Carbon::parse('2021-01-01 00:00:00'))
            ->until(Carbon::parse('2021-01-06 00:00:00'))
            ->groupBy(SubscriptionHistory::PERIOD_DAY);

        $histogram = $history->get();

        $this->assertEquals([
            'start' => '2021-01-01 00:00:00',
            'end' => '2021-01-02 00:00:00',
            'count' => 1,
            'new' => 1,
            'trial' => 1,
            'grace' => 1,
            'ended' => 0,
        ], $histogram->get('2021-01-01'));
        $this->assertEquals([
            'start' => '2021-01-02 00:00:00',
            'end' => '2021-01-03 00:00:00',
            'count' => 1,
            'new' => 0,
            'trial' => 1,
            'grace' => 1,
            'ended' => 0,
        ], $histogram->get('2021-01-02'));
        $this->assertEquals([
            'start' => '2021-01-03 00:00:00',
            'end' => '2021-01-04 00:00:00',
            'count' => 1,
            'new' => 0,
            'trial' => 1,
            'grace' => 1,
            'ended' => 0,
        ], $histogram->get('2021-01-03'));
        $this->assertEquals([
            'start' => '2021-01-04 00:00:00',
            'end' => '2021-01-05 00:00:00',
            'count' => 2,
            'new' => 1,
            'trial' => 1,
            'grace' => 2,
            'ended' => 0,
        ], $histogram->get('2021-01-04'));
        $this->assertEquals([
            'start' => '2021-01-05 00:00:00',
            'end' => '2021-01-06 00:00:00',
            'count' => 2,
            'new' => 0,
            'trial' => 0,
            'grace' => 2,
            'ended' => 2,
        ], $histogram->get('2021-01-05'));
    }

    /** @test */
    public function it_can_display_historical_data_for_subscriptions_partitioned_by_month()
    {
        $testUser = new TestUser(['id' => 1]);
        $testUser->save();
        $subscription0 = $testUser->newSubscription()->trialDays(3)->create();
        $subscription0->update(['created_at' => '2020-12-30 09:00:00']);

        $subscription1 = $testUser->newSubscription()->trialDays(3)->create();
        $subscription1->update(['created_at' => '2021-01-01 09:00:00']);
        $subscription1->update(['trial_ends_at' => '2021-01-04 09:00:00']);

        $subscription2 = $testUser->newSubscription()->create();
        $subscription2->update(['created_at' => '2021-01-04 09:00:00']);

        $subscription1->cancelAt(Carbon::parse('2021-01-05'));
        $subscription2->cancelAt(Carbon::parse('2021-01-05'));

        $history = (new SubscriptionHistory())
            ->from(Carbon::parse('2021-01-01 00:00:00'))
            ->until(Carbon::parse('2021-01-06 00:00:00'))
            ->groupBy(SubscriptionHistory::PERIOD_MONTH);

        $histogram = $history->get();

        $this->assertEquals([
            'start' => '2021-01-01 00:00:00',
            'end' => '2021-02-01 00:00:00',
            'count' => 3,
            'new' => 2,
            'trial' => 2,
            'grace' => 2,
            'ended' => 2,
        ], $histogram->get('2021-01'));
    }
}
