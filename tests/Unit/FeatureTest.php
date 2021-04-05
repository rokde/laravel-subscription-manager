<?php

namespace Rokde\SubscriptionManager\Tests\Unit;

use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Tests\TestCase;

class FeatureTest extends TestCase
{
    /** @test */
    public function a_feature_can_resist_in_many_plans()
    {
        /** @var Feature $feature1 */
        $feature1 = Feature::factory()->create();
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create();
        /** @var Plan $planA */
        $planA = Plan::factory()->create();
        /** @var Plan $planB */
        $planB = Plan::factory()->create();

        $feature1->plans()->attach($planA);
        $feature1->plans()->attach($planB);
        $feature2->plans()->attach($planA);

        $this->assertCount(2, $planA->features);
        $this->assertCount(1, $planB->features);
        $this->assertCount(2, $feature1->plans);
        $this->assertCount(1, $feature2->plans);

    }
}
