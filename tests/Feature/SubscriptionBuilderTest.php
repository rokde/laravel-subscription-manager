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

        $this->assertTrue($subscription->isOnTrial());
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

        $this->assertFalse($subscription->isOnTrial());
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

        $this->assertFalse($subscription->isRecurring());
        $this->assertNull($subscription->period);
        $this->assertEquals(CarbonInterval::years(1000), $subscription->periodLength());
    }

    /** @test */
    public function it_can_create_a_subscription_with_a_period_length()
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
        $subscription = $builder->periodLength('P1D')
            ->create();

        $this->assertTrue($subscription->isRecurring());
        $this->assertEquals('P1D', $subscription->period);
        $this->assertEquals(CarbonInterval::day(), $subscription->periodLength());
    }

    /** @test */
    public function it_can_create_a_subscription_with_a_period_length_with_date_interval()
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
        $subscription = $builder->periodLength(\DateInterval::createFromDateString('1 week'))
            ->create();

        $this->assertTrue($subscription->isRecurring());
        $this->assertEquals('P7D', $subscription->period);
        $this->assertEquals(CarbonInterval::week(), $subscription->periodLength());
    }
}
