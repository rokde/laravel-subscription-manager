<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Subscriptions;

use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Subscription;

class CreateSubscriptionFromFeaturesAction
{
    /**
     * create a subscription from plan for subscribable
     *
     * @param array $featureCodes
     * @param \Illuminate\Database\Eloquent\Model $subscribable
     * @param \callable|null $callback for modifying the internal SubscriptionBuilder instance
     * @return \Rokde\SubscriptionManager\Models\Subscription
     */
    public function execute(array $featureCodes, Model $subscribable, $callback = null): Subscription
    {
        $factory = (new SubscriptionBuilder($subscribable))
            ->withFeatures($featureCodes);
        if ($callback !== null) {
            $callback($factory);
        }

        return $factory->create();
    }
}
