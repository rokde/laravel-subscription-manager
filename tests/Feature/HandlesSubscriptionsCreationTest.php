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

class HandlesSubscriptionsCreationTest extends TestCase
{
    /** @test */
    public function it_can_subscribe_a_model()
    {
        $model = new class extends Model {
            use Subscribable;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                $this->id = 1;
            }
        };

        /** @var SubscriptionBuilder $builder */
        $builder = $model->newSubscription();

        $this->assertInstanceOf(SubscriptionBuilder::class, $builder);

        $subscription = $builder->create();
        $this->assertInstanceOf(Subscription::class, $subscription);

        $this->assertCount(1, $model->subscriptions);
        $this->assertEquals($model->subscription()->first()->getKey(), $subscription->getKey());

        $this->assertEmpty($subscription->features);
        $this->assertFalse($subscription->hasFeature('foo'));
    }

    /** @test */
    public function it_can_subscribe_a_model_with_features_by_plan()
    {
        $model = new class extends Model {
            use Subscribable;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                $this->id = 1;
            }
        };

        /** @var Feature $feature1 */
        $feature1 = Feature::factory()->create(['code' => 'f1']);
        /** @var Feature $feature2 */
        $feature2 = Feature::factory()->create(['code' => 'f2']);
        /** @var Plan $planA */
        $planA = Plan::factory()->create(['name' => 'a']);

        $planA->features()->attach($feature1);
        $planA->features()->attach($feature2);

        /** @var SubscriptionBuilder $builder */
        $builder = $model->newSubscription($planA);

        $this->assertInstanceOf(SubscriptionBuilder::class, $builder);

        $subscription = $builder->create();
        $this->assertInstanceOf(Subscription::class, $subscription);

        $this->assertCount(1, $model->subscriptions);
        $this->assertEquals($model->subscription()->first()->getKey(), $subscription->getKey());

        $this->assertCount(2, $subscription->features);
        $this->assertTrue($subscription->hasFeature('f1'));
        $this->assertFalse($subscription->hasFeature('f3'));

        $this->assertTrue($model->onPlan($planA));
    }

    /** @test */
    public function it_can_subscribe_a_model_with_features_by_params()
    {
        $model = new class extends Model {
            use Subscribable;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                $this->id = 1;
            }
        };

        /** @var SubscriptionBuilder $builder */
        $builder = $model->newFeatureSubscription(['f1', 'f2']);

        $this->assertInstanceOf(SubscriptionBuilder::class, $builder);

        $subscription = $builder->create();
        $this->assertInstanceOf(Subscription::class, $subscription);

        $this->assertCount(1, $model->subscriptions);
        $this->assertEquals($model->subscription()->first()->getKey(), $subscription->getKey());
        $this->assertEquals(['f1', 'f2'], $model->subscribedFeatures());

        $this->assertCount(2, $subscription->features);
        $this->assertTrue($subscription->hasFeature('f1'));
        $this->assertFalse($subscription->hasFeature('f3'));

        $this->assertTrue($subscription->isValid());
        $this->assertFalse($subscription->isOnTrial());
        $this->assertFalse($subscription->isOnGracePeriod());
        $this->assertFalse($subscription->isCancelled());
        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->isRecurring());
        $this->assertFalse($subscription->isEnded());
    }

    /** @test */
    public function it_can_merge_two_subscriptions()
    {
        $model = new class extends Model {
            use Subscribable;

            public function __construct(array $attributes = [])
            {
                parent::__construct($attributes);

                $this->id = 1;
            }
        };

        /** @var SubscriptionBuilder $builder */
        $model->newFeatureSubscription(['f1'])->create();
        $model->newFeatureSubscription(['f3', 'f2'])->create();

        $this->assertEquals(['f1', 'f2', 'f3'], $model->subscribedFeatures());
    }

    /** @test */
    public function it_respects_only_active_subscriptions()
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
        $active = $model->newFeatureSubscription(['f1'])->create();
        /** @var Subscription $inactive */
        $inactive = $model->newFeatureSubscription(['f3', 'f2'])->create();
        $inactive->update([
            'ends_at' => Carbon::now()->subDay(),
        ]);

        $this->assertEquals(['f1'], $model->subscribedFeatures());
        $this->assertEquals($active->getKey(), $model->activeSubscriptions->first()->getKey());
        $this->assertEquals($inactive->getKey(), $model->subscriptions[0]->getKey());
        $this->assertEquals($active->getKey(), $model->subscriptions[1]->getKey());
    }
}
