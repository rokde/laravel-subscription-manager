<?php

namespace Rokde\SubscriptionManager\Tests\Unit;

use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Tests\TestCase;

class PlanTest extends TestCase
{
    /** @test */
    public function a_plan_can_have_many_features()
    {
        /** @var Feature $feature1 */
        $feature1 = Feature::factory()->create();
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create();
        /** @var Plan $planA */
        $planA = Plan::factory()->create();
        /** @var Plan $planB */
        $planB = Plan::factory()->create();

        $planA->features()->attach($feature1);
        $planA->features()->attach($feature2);
        $planB->features()->attach($feature1);

        $this->assertCount(2, $planA->features);
        $this->assertCount(1, $planB->features);
        $this->assertCount(2, $feature1->plans);
        $this->assertCount(1, $feature2->plans);
    }
}
