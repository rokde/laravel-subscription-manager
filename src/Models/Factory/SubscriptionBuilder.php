<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Factory;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Models\SubscriptionFeature;

class SubscriptionBuilder
{
    protected Model $subscribable;
    protected ?Plan $plan;
    protected ?int $trialDays = null;
    protected bool $skipTrial = false;
    protected array $features = [];
    protected ?string $period = 'P1Y';

    /**
     * SubscriptionBuilder constructor.
     * @param \Illuminate\Database\Eloquent\Model $subscribable
     * @param \Rokde\SubscriptionManager\Models\Plan|null $plan
     */
    public function __construct(Model $subscribable, ?Plan $plan = null)
    {
        $this->subscribable = $subscribable;
        $this->plan = $plan;

        if ($this->plan instanceof Plan) {
            $this->withFeaturesFromPlan($plan);
        }
    }

    /**
     * Use the given features
     *
     * @param array|string[]|\Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\Feature[] $features
     * @return $this
     */
    public function withFeatures($features): self
    {
        foreach ($features as $feature) {
            if ($feature instanceof Feature) {
                $this->features[] = SubscriptionFeature::fromFeature($feature);
                continue;
            }

            $this->features[] = SubscriptionFeature::fromCode($feature);
        }

        return $this;
    }

    public function withFeaturesFromPlan(Plan $plan): self
    {
        $plan->features->each(function (Feature $feature) {
            $this->features[] = SubscriptionFeature::fromFeature($feature);
        });

        return $this;
    }

    /**
     * set trial days
     *
     * @param int $trialDays
     * @return $this
     */
    public function trialDays(int $trialDays): self
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    /**
     * set trial days to null
     *
     * @return $this
     */
    public function skipTrial(): self
    {
        $this->skipTrial = true;
        if ($this->trialDays === null) {
            $this->trialDays = 1;
        }

        return $this;
    }

    /**
     * unset period string
     *
     * @return $this
     */
    public function infinitePeriod(): self
    {
        $this->period = null;

        return $this;
    }

    /**
     * Set period
     *
     * @param \DateInterval|string $period
     * @return $this
     */
    public function periodLength($period): self
    {
        if ($period instanceof \DateInterval) {
            $period = CarbonInterval::getDateIntervalSpec($period);
        }

        $this->period = $period;

        return $this;
    }

    /**
     * Creates the subscription with all values already set
     *
     * @return \Rokde\SubscriptionManager\Models\Subscription
     */
    public function create(): Subscription
    {
        /** @var Subscription $subscription */
        $subscription = $this->subscribable->subscriptions()->create([
            'plan_id' => $this->plan instanceof Plan
                ? $this->plan->getKey()
                : null,
            'period' => $this->period,
            'trial_ends_at' => $this->getTrialEnd(),
            'ends_at' => null,
        ]);

        $subscription->features()->saveMany($this->features);

        return $subscription;
    }

    /**
     * Returns trial end with given parameters
     *
     * @return \DateTimeInterface|null
     */
    protected function getTrialEnd(): ?\DateTimeInterface
    {
        return $this->skipTrial
            ? null
            : (
                $this->trialDays !== null
                ? Carbon::now()->addDays($this->trialDays)
                : null
            );
    }
}
