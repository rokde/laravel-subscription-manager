<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Plans;

use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Plan;

class AttachFeatureToPlanAction
{
    /**
     * attaches a feature to a plan; metered features should have a default quota
     */
    public function execute(Plan $plan, Feature $feature, ?int $defaultQuota = null): Plan
    {
        //  normalize default quota
        if (! $feature->metered) {
            $defaultQuota = null;
        } else {
            //  cast to integer
            $defaultQuota = intval($defaultQuota);
            $defaultQuota = max(0, $defaultQuota);
        }

        $plan->features()->attach($feature, ['default_quota' => $defaultQuota]);

        return $plan;
    }
}
