<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\SubscriptionFeatures;

use Rokde\SubscriptionManager\Models\Feature;
use Rokde\SubscriptionManager\Models\Subscription;
use Rokde\SubscriptionManager\Models\SubscriptionFeature;

class SetFeatureQuotaAction
{
    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function executeForSubscription(Subscription $subscription, Feature|string $featureOrCode, int $quota): SubscriptionFeature
    {
        $code = $featureOrCode instanceof Feature
            ? $featureOrCode->code
            : $featureOrCode;

        /** @var SubscriptionFeature $subscriptionFeature */
        $subscriptionFeature = $subscription->features()
            ->where('code', $code)
            ->firstOrFail();

        return $this->execute($subscriptionFeature, $quota);
    }

    public function execute(SubscriptionFeature $feature, int $quota): SubscriptionFeature
    {
        if (! $feature->isMetered()) {
            $feature->update(['quota' => $quota]);
        }

        return $feature;
    }
}
