<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Feature;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Concerns\Subscribable;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Tests\TestCase;

class SubscriptionBuilderTest extends TestCase
{
    /** @test */
    public function it_can_create_subscription_with_trial()
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
        $subscription = $builder->trialDays(30)
            ->create();

        $this->assertTrue($subscription->onTrial());
        $this->assertEquals(Carbon::now()->addDays(30)->toDateString(), $subscription->trial_ends_at->toDateString());
    }

    /** @test */
    public function it_can_create_subscription_without_trial()
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
        $subscription = $builder->skipTrial()
            ->create();

        $this->assertFalse($subscription->onTrial());
        $this->assertNull($subscription->trial_ends_at);
    }

    /** @test */
    public function it_can_create_an_infinite_subscription()
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
        $subscription = $builder->infinitePeriod()
            ->create();

        $this->assertFalse($subscription->recurring());
        $this->assertNull($subscription->period);
        $this->assertEquals(CarbonInterval::years(1000), $subscription->periodLength());
    }
}
