<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Actions;

use Rokde\SubscriptionManager\Actions\Plans\CreatePlanAction;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Tests\TestCase;

class CreatePlanActionTest extends TestCase
{
    /** @test */
    public function it_can_create_a_plan()
    {
        $plan = (new CreatePlanAction())->execute('planA');
        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertTrue($plan->exists);
    }
}
