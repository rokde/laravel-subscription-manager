<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Factory;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;

class SubscriptionBuilder
{
    protected Model $subscribable;
    protected ?Plan $plan;
    protected ?int $trialDays = null;
    protected bool $skipTrial = false;
    protected array $features = [];
    protected ?string $period = 'P1Y';

    public function __construct(Model $subscribable, ?Plan $plan = null)
    {
        $this->subscribable = $subscribable;
        $this->plan = $plan;

        if ($this->plan instanceof Plan) {
            $this->withFeatures($plan->features);
        }
    }

    /**
     * @param array|string[]|Collection|Feature[] $features
     * @return $this
     */
    public function withFeatures($features): self
    {
        $this->features = is_array($features)
            ? $features
            : $features->pluck('code')->all();

        return $this;
    }

    public function trialDays(int $trialDays): self
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    public function skipTrial(): self
    {
        $this->skipTrial = true;
        if ($this->trialDays === null) {
            $this->trialDays = 1;
        }

        return $this;
    }

    public function infinitePeriod(): self
    {
        $this->period = null;

        return $this;
    }

    /**
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

    public function create(): Subscription
    {
        return $this->subscribable->subscriptions()->create([
            'plan_id' => $this->plan instanceof Plan
                ? $this->plan->getKey()
                : null,
            'features' => $this->features,
            'period' => $this->period,
            'trial_ends_at' => $this->getTrialEnd(),
            'ends_at' => null,
        ]);
    }

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
