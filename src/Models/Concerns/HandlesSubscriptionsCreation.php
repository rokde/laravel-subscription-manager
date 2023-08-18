<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Plan;

/**
 * Trait HandlesSubscriptionsCreation
 * @package Rokde\SubscriptionManager\Models\Concerns
 *
 * @property-read \Illuminate\Database\Eloquent\Model $this
 */
trait HandlesSubscriptionsCreation
{
    /** Creates a subscription. When a plan is given, the plan features will be assigned. */
    public function newSubscription(?Plan $plan = null): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $plan);
    }

    /**
     * Creates a subscription with a set of features assigned.
     *
     * @param @param array<string>|\Illuminate\Database\Eloquent\Collection<\Rokde\SubscriptionManager\Models\Feature> $features
     */
    public function newFeatureSubscription(array|Collection $features): SubscriptionBuilder
    {
        return (new SubscriptionBuilder($this))
            ->withFeatures($features);
    }
}
