<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Concerns\Subscribable;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscribableTest extends TestCase
{
    /** @test */
    public function it_can_ask_for_active_subscriptions()
    {
        $model = new class extends Model {
            use Subscribable;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                $this->id = 1;
            }
        };

        /** @var Subscription $active */
        $active = $model->newSubscription()->create();
        /** @var Subscription $inactive */
        $inactive = $model->newSubscription()->create();
        $inactive->update([
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertEquals($active->getKey(), $model->activeSubscriptions->first()->getKey());
        $this->assertEquals($active->getKey(), $model->subscription->getKey());
        $this->assertEquals($inactive->getKey(), $model->subscriptions[0]->getKey());
        $this->assertEquals($active->getKey(), $model->subscriptions[1]->getKey());
    }

    /** @test */
    public function it_can_ask_for_inactive_subscriptions()
    {
        $model = new class extends Model {
            use Subscribable;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                $this->id = 1;
            }
        };

        /** @var Subscription $inactive */
        $inactive = $model->newSubscription()->create();
        $inactive->update([
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertTrue($model->everSubscribed());
        $this->assertFalse($model->subscribed());
    }
}
