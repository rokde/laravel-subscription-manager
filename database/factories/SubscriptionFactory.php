<?php

namespace Rokde\SubscriptionManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rokde\SubscriptionManager\Models\Subscription;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'subscribable_type' => 'test',
            'subscribable_id' => 1,
            'plan_id' => null,
            'features' => ['feature-1', 'feature-2'],
        ];
    }
}
