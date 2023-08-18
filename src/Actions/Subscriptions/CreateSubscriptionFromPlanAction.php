<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Subscriptions;

use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder;
use Rokde\SubscriptionManager\Models\Plan;
use Rokde\SubscriptionManager\Models\Subscription;

class CreateSubscriptionFromPlanAction
{
    /**
     * create a subscription from plan for subscribable
     */
    public function execute(Plan $plan, Model $subscribable, \callable|\Closure|null $callback = null): Subscription
    {
        $factory = new SubscriptionBuilder($subscribable, $plan);
        if ($callback !== null) {
            $callback($factory);
        }

        return $factory->create();
    }
}
