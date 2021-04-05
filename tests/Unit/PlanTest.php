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
        $feature1 = Feature::factory()->create(['code' => 'f1']);
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create(['code' => 'f2']);
        /** @var Plan $planA */
        $planA = Plan::factory()->create(['name' => 'a']);
        /** @var Plan $planB */
        $planB = Plan::factory()->create(['name' => 'b']);

        $planA->features()->attach($feature1);
        $planA->features()->attach($feature2);
        $planB->features()->attach($feature1);

        $this->assertCount(2, Plan::whereName('a')->first()->features);
        $this->assertCount(1, Plan::whereName('b')->first()->features);
        $this->assertCount(2, Feature::whereCode('f1')->first()->plans);
        $this->assertCount(1, Feature::whereCode('f2')->first()->plans);
    }
}
