<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Commands;

use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Tests\TestCase;

class FeaturesListCommandTest extends TestCase
{
    /** @test */
    public function it_can_ask_for_empty_features()
    {
        $this->artisan('features:list')
            ->expectsTable(['feature', 'plans'], [])
            ->run();
    }

    /** @test */
    public function it_can_ask_for_features()
    {
        /** @var Feature $feature1 */
        $feature1 = Feature::factory()->create(['code' => 'last-feature']);
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create(['code' => 'first-feature']);

        $this->artisan('features:list')
            ->expectsTable(['feature', 'plans'], [
                ['first-feature', ''],
                ['last-feature', ''],
            ])
            ->run();
    }
}
