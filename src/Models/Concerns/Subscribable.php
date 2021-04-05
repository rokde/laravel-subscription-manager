<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;

/**
 * Trait Subscribable
 * @package Rokde\SubscriptionManager\Models\Concerns
 *
 * @property-read \Illuminate\Database\Eloquent\Model $this
 * @property-read \Illuminate\Database\Eloquent\Collection|Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read null|Subscription $subscription
 * @mixin \Eloquent
 */
trait Subscribable
{
    public function newSubscription(?Plan $plan = null): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $plan);
    }

    /**
     * @param @param array|string[]|\Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\Feature[] $features
     * @return \Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder
     */
    public function newFeatureSubscription($features): SubscriptionBuilder
    {
        return (new SubscriptionBuilder($this))
            ->withFeatures($features);
    }

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscribable')
            ->latest();
    }

    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable')
            ->latest();
    }

    /**
     * @param null|string|\Rokde\SubscriptionManager\Models\Feature $feature
     * @return bool
     */
    public function subscribed($feature = null): bool
    {
        return $this->subscriptions
                ->first(function (Subscription $subscription) use ($feature) {
                    return $subscription->valid() && ($feature === null || $subscription->hasFeature($feature));
                }) !== null;
    }

    public function onPlan(Plan $plan): bool
    {
        return $this->subscriptions()
                ->where('plan_id', $plan->getKey())
                ->get()
                ->first(function (Subscription $subscription) {
                    return $subscription->valid();
                }) !== null;
    }
}
