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
}
