<?php

namespace Rokde\SubscriptionManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
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
            'uuid' => (string) Str::uuid(),
            'period' => 'P1Y',
        ];
    }
}
