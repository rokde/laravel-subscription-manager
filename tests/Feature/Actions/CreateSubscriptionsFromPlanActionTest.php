<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Actions;

use Rokde\SubscriptionManager\Actions\Features\CreateFeatureAction;
use Rokde\SubscriptionManager\Actions\Plans\AttachFeatureToPlanAction;
use Rokde\SubscriptionManager\Actions\Plans\CreatePlanAction;
use Rokde\SubscriptionManager\Actions\Subscriptions\CreateSubscriptionFromPlanAction;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;
use Rokde\SubscriptionManager\Tests\TestUser;

class CreateSubscriptionsFromPlanActionTest extends TestCase
{
    /** @test */
    public function it_can_create_a_subscription_from_plan_with_yearly_recurring_subscription_by_default()
    {
        $plan = (new CreatePlanAction())->execute('planA');
        $feature = (new CreateFeatureAction())->execute('f1');
        $meteredFeature = (new CreateFeatureAction())->execute('f2', true);

        $planA = (new AttachFeatureToPlanAction())->execute($plan, $feature);
        $planA = (new AttachFeatureToPlanAction())->execute($planA, $meteredFeature, 100);

        $user = new TestUser();
        $user->save();

        $subscription = (new CreateSubscriptionFromPlanAction())->execute($planA, $user);

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
        $this->assertEquals(100, $subscription->features->last()->quota);
        $this->assertEquals(0, $subscription->features->last()->used);
        $this->assertEquals(100, $subscription->features->last()->remaining);
        $this->assertTrue($subscription->features->last()->metered);
        $this->assertTrue($subscription->features->last()->isMetered());
        $this->assertTrue($subscription->features->last()->isUsable());

        $this->assertEquals($subscription->getKey(), $subscription->features->first()->subscription->getKey());
    }

    /** @test */
    public function it_can_create_a_subscription_from_plan_with_modifying_builder()
    {
        $plan = (new CreatePlanAction())->execute('planA');
        $feature = (new CreateFeatureAction())->execute('f1');

        $planA = (new AttachFeatureToPlanAction())->execute($plan, $feature);

        $user = new TestUser();
        $user->save();

        $subscription = (new CreateSubscriptionFromPlanAction())->execute($planA, $user, function (SubscriptionBuilder $factory) {
            $factory->infinitePeriod()
                ->skipTrial();
        });

        $this->assertNull($subscription->period);
        $this->assertFalse($subscription->isRecurring());
        $this->assertTrue($subscription->isInfinite());
        $this->assertNull($subscription->trial_ends_at);
    }
}
