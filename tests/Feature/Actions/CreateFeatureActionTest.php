<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature\Actions;

use Rokde\SubscriptionManager\Actions\Features\CreateFeatureAction;
use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Tests\TestCase;

class CreateFeatureActionTest extends TestCase
{
    /** @test */
    public function it_can_create_a_feature()
    {
        $feature = (new CreateFeatureAction())->execute('f1');
        $this->assertInstanceOf(Feature::class, $feature);
        $this->assertTrue($feature->exists);
        $this->assertFalse($feature->metered);
    }

    /** @test */
    public function it_can_create_a_metered_feature()
    {
        $feature = (new CreateFeatureAction())->execute('f1', true);
        $this->assertInstanceOf(Feature::class, $feature);
        $this->assertTrue($feature->exists);
        $this->assertTrue($feature->metered);
    }
}
