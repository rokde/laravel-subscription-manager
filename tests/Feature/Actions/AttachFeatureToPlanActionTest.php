<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Actions;

use Rokde\SubscriptionManager\Actions\Features\CreateFeatureAction;
use Rokde\SubscriptionManager\Actions\Plans\AttachFeatureToPlanAction;
use Rokde\SubscriptionManager\Actions\Plans\CreatePlanAction;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Tests\TestCase;

class AttachFeatureToPlanActionTest extends TestCase
{
    /** @test */
    public function it_can_attach_a_feature_to_a_plan()
    {
        $feature = (new CreateFeatureAction())->execute('f1');
        $plan = (new CreatePlanAction())->execute('planA');

        $planA = (new AttachFeatureToPlanAction())->execute($plan, $feature);
        $this->assertInstanceOf(Plan::class, $planA);
        $this->assertCount(1, $planA->features);
        $this->assertNull($planA->features->first()->pivot->default_quota);
    }

    /** @test */
    public function it_can_attach_a_metered_feature_to_a_plan()
    {
        $feature = (new CreateFeatureAction())->execute('f1', true);
        $plan = (new CreatePlanAction())->execute('planA');

        $planA = (new AttachFeatureToPlanAction())->execute($plan, $feature);
        $this->assertInstanceOf(Plan::class, $planA);
        $this->assertCount(1, $planA->features);
        $this->assertEquals(0, $planA->features->first()->pivot->default_quota);
    }

    /** @test */
    public function it_can_attach_a_metered_feature_to_a_plan_with_default_quota()
    {
        $feature = (new CreateFeatureAction())->execute('f1', true);
        $plan = (new CreatePlanAction())->execute('planA');

        $planA = (new AttachFeatureToPlanAction())->execute($plan, $feature, 100);
        $this->assertInstanceOf(Plan::class, $planA);
        $this->assertCount(1, $planA->features);
        $this->assertEquals(100, $planA->features->first()->pivot->default_quota);
    }
}
