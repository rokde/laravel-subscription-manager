<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Actions;

use Rokde\SubscriptionManager\Actions\Features\CreateFeatureAction;
use Rokde\SubscriptionManager\Actions\Subscriptions\CreateSubscriptionFromFeaturesAction;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;
use Rokde\SubscriptionManager\Tests\TestUser;

class CreateSubscriptionFromFeaturesActionTest extends TestCase
{
    /** @test */
    public function it_can_create_a_subscription_from_feature_codes_with_yearly_recurring_subscription_by_default()
    {
        $user = new TestUser();
        $user->save();

        $subscription = (new CreateSubscriptionFromFeaturesAction())->execute(['f1', 'f2'], $user);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertCount(2, $subscription->features);
        $this->assertEquals('P1Y', $subscription->period);
        $this->assertTrue($subscription->isRecurring());
        $this->assertEquals('f1', $subscription->features->first()->code);
        $this->assertEquals(0, $subscription->features->first()->remaining);
        $this->assertFalse($subscription->features->first()->metered);
        $this->assertFalse($subscription->features->first()->isMetered());
        $this->assertTrue($subscription->features->first()->isUsable());
        $this->assertCount(0, $subscription->features->first()->usages);
        $this->assertEquals('f2', $subscription->features->last()->code);
        $this->assertNull($subscription->features->last()->quota);
        $this->assertNull($subscription->features->last()->used);
        $this->assertEquals(0, $subscription->features->last()->remaining);
        $this->assertFalse($subscription->features->last()->metered);
        $this->assertFalse($subscription->features->last()->isMetered());
        $this->assertTrue($subscription->features->last()->isUsable());

        $this->assertEquals($subscription->getKey(), $subscription->features->first()->subscription->getKey());
    }

    /** @test */
    public function it_can_create_a_subscription_from_feature_codes_with_modifying_builder()
    {
        $user = new TestUser();
        $user->save();

        $subscription = (new CreateSubscriptionFromFeaturesAction())->execute(
            ['f1', 'f2'],
            $user,
            function (SubscriptionBuilder $factory) {
                $factory->infinitePeriod()
                    ->skipTrial();
            }
        );

        $this->assertNull($subscription->period);
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isInfinite());
        $this->assertNull($subscription->trial_ends_at);
    }

    /** @test */
    public function it_can_create_a_subscription_from_features_with_yearly_recurring_subscription_by_default()
    {
        $feature = (new CreateFeatureAction())->execute('f1');
        $meteredFeature = (new CreateFeatureAction())->execute('f2', true);

        $user = new TestUser();
        $user->save();

        $subscription = (new CreateSubscriptionFromFeaturesAction())->execute([$feature, $meteredFeature], $user);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertCount(2, $subscription->features);
        $this->assertEquals('P1Y', $subscription->period);
        $this->assertTrue($subscription->isRecurring());
        $this->assertEquals('f1', $subscription->features->first()->code);
        $this->assertEquals(0, $subscription->features->first()->remaining);
        $this->assertFalse($subscription->features->first()->metered);
        $this->assertFalse($subscription->features->first()->isMetered());
        $this->assertTrue($subscription->features->first()->isUsable());
        $this->assertCount(0, $subscription->features->first()->usages);
        $this->assertEquals('f2', $subscription->features->last()->code);
        $this->assertEquals(0, $subscription->features->last()->quota);
        $this->assertEquals(0, $subscription->features->last()->used);
        $this->assertEquals(0, $subscription->features->last()->remaining);
        $this->assertTrue($subscription->features->last()->metered);
        $this->assertTrue($subscription->features->last()->isMetered());
        $this->assertFalse($subscription->features->last()->isUsable());

        $this->assertEquals($subscription->getKey(), $subscription->features->first()->subscription->getKey());
    }

    /** @test */
    public function it_can_set_quota_on_creating_by_action()
    {
        $feature = (new CreateFeatureAction())->execute('f1');
        $meteredFeature = (new CreateFeatureAction())->execute('f2', true);

        $user = new TestUser();
        $user->save();

        $subscription = (new CreateSubscriptionFromFeaturesAction())->execute([$feature, $meteredFeature], $user, function (SubscriptionBuilder $builder) {
            $builder->setQuota('f2', 10);
        });

        $this->assertEquals(10, $subscription->features->last()->quota);
        $this->assertEquals(0, $subscription->features->last()->used);
        $this->assertEquals(10, $subscription->features->last()->remaining);
    }
}
