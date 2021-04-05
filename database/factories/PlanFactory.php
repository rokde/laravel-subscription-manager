<?php

namespace Rokde\SubscriptionManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rokde\SubscriptionManager\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
