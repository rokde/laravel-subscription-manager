<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature;

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

        $this->assertCount(2, $subscription->features);
        $this->assertTrue($subscription->hasFeature('f1'));
        $this->assertFalse($subscription->hasFeature('f3'));

        $this->assertTrue($subscription->valid());
        $this->assertFalse($subscription->onTrial());
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->cancelled());
        $this->assertTrue($subscription->active());
        $this->assertTrue($subscription->recurring());
        $this->assertFalse($subscription->ended());
    }
}
