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
        $feature1 = Feature::factory()->create(['code' => 'f1']);
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create(['code' => 'f2']);
        /** @var Plan $planA */
        $planA = Plan::factory()->create(['name' => 'a']);
        /** @var Plan $planB */
        $planB = Plan::factory()->create(['name' => 'b']);

        $feature1->plans()->attach($planA);
        $feature1->plans()->attach($planB);
        $feature2->plans()->attach($planA);

        $this->assertCount(2, Plan::whereName('a')->first()->features);
        $this->assertCount(1, Plan::whereName('b')->first()->features);
        $this->assertCount(2, Feature::whereCode('f1')->first()->plans);
        $this->assertCount(1, Feature::whereCode('f2')->first()->plans);
    }
}
