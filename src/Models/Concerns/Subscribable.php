<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;

/**
 * Trait Subscribable
 * @package Rokde\SubscriptionManager\Models\Concerns
 *
 * @property-read \Illuminate\Database\Eloquent\Model $this
 * @property-read \Illuminate\Database\Eloquent\Collection|Subscription[] $activeSubscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|Subscription[] $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read null|Subscription $subscription
 * @mixin \Eloquent
 */
trait Subscribable
{
    use HandlesSubscriptionsCreation;

    public function subscriptions(): MorphMany
    {
        return $this->morphMany(Subscription::class, 'subscribable')
            ->latest('id');
    }

    public function activeSubscriptions(): MorphMany
    {
        return $this->subscriptions()
            ->active();
    }

    public function subscription(): MorphOne
    {
        return $this->morphOne(Subscription::class, 'subscribable')
            ->active()
            ->latest('id');
    }

    /** Was the subscribable ever subscribed? When feature given only subscriptions with this feature will be checked. */
    public function everSubscribed(string|Feature|null $feature = null): bool
    {
        return $this->subscriptions
                ->first(function (Subscription $subscription) use ($feature) {
                    return $feature === null || $subscription->hasFeature($feature);
                }) !== null;
    }

    /** Is the subscribable subscribed? When feature given only subscriptions with this feature will be checked. */
    public function subscribed(string|Feature|null $feature = null): bool
    {
        return $this->activeSubscriptions
                ->first(function (Subscription $subscription) use ($feature) {
                    return $subscription->isValid()
                        && ($feature === null || $subscription->hasFeature($feature));
                }) !== null;
    }

    /**
     * returns all active subscribed features
     *
     * @return array<string>
     */
    public function subscribedFeatures(): array
    {
        return $this->activeSubscriptions->flatMap(function (Subscription $subscription) {
            return $subscription->features()->pluck('code');
        })
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /** Is the subscribable actively subscribed on a plan? */
    public function onPlan(Plan $plan): bool
    {
        return $this->activeSubscriptions()
                ->where('plan_id', $plan->getKey())
                ->get()
                ->first(function (Subscription $subscription) {
                    return $subscription->isValid();
                }) !== null;
    }
}
