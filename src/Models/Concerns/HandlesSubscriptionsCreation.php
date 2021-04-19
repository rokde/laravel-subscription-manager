<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Concerns;

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
    /**
     * Creates a subscription. When a plan is given, the plan features will be assigned.
     *
     * @param \Rokde\SubscriptionManager\Models\Plan|null $plan
     * @return \Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder
     */
    public function newSubscription(?Plan $plan = null): SubscriptionBuilder
    {
        return new SubscriptionBuilder($this, $plan);
    }

    /**
     * Creates a subscription with a set of features assigned.
     *
     * @param @param array|string[]|\Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\Feature[] $features
     * @return \Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder
     */
    public function newFeatureSubscription($features): SubscriptionBuilder
    {
        return (new SubscriptionBuilder($this))
            ->withFeatures($features);
    }
}
