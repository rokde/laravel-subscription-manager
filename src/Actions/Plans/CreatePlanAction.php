<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Plans;

use Rokde\SubscriptionManager\Models\Plan;

class CreatePlanAction
{
    /**
     * @param string $name
     * @return \Rokde\SubscriptionManager\Models\Plan
     */
    public function execute(string $name): Plan
    {
        return Plan::forceCreate([
            'name' => $name,
        ]);
    }
}
