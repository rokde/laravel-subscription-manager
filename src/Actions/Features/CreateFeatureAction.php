<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Actions\Features;

use Rokde\SubscriptionManager\Models\Feature;

class CreateFeatureAction
{
    public function execute(string $code, bool $metered = false): Feature
    {
        return Feature::forceCreate([
            'code' => $code,
            'metered' => $metered,
        ]);
    }
}
