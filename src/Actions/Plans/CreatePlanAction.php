<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Plans;

use Rokde\SubscriptionManager\Models\Plan;

class CreatePlanAction
{
    public function execute(string $name): Plan
    {
        return Plan::forceCreate([
            'name' => $name,
        ]);
    }
}
