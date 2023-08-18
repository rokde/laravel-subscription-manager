<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Subscriptions;

use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Subscription;

class CreateSubscriptionFromFeaturesAction
{
    /**
     * create a subscription from plan for subscribable
     */
    public function execute(array $featureCodes, Model $subscribable, \callable|\Closure|null $callback = null): Subscription
    {
        $factory = (new SubscriptionBuilder($subscribable))
            ->withFeatures($featureCodes);
        if ($callback !== null) {
            $callback($factory);
        }

        return $factory->create();
    }
}
