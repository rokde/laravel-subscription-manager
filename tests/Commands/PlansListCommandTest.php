<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Commands;

use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Tests\TestCase;

class PlansListCommandTest extends TestCase
{
    /** @test */
    public function it_can_ask_for_empty_plans()
    {
        $this->artisan('plans:list')
            ->expectsTable(['name', 'features'], [])
            ->run();
    }

    /** @test */
    public function it_can_ask_for_plans_with_features()
    {
        /** @var Feature $feature1 */
        $feature1 = Feature::factory()->create(['code' => 'f1']);
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create(['code' => 'f2']);
        /** @var Plan $planA */
        $planA = Plan::factory()->create(['name' => 'A']);
        $planA->features()->attach($feature1);

        /** @var Plan $planB */
        $planB = Plan::factory()->create(['name' => 'B']);
        $planB->features()->attach($feature1);
        $planB->features()->attach($feature2);

        $this->artisan('plans:list')
            ->assertExitCode(0)
            ->expectsTable(['name', 'features'], [
                ['A', 'f1'],
                ['B', 'f1, f2'],
            ])
            ->run();
    }
}
