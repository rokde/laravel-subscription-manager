<?php

namespace Rokde\SubscriptionManager\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rokde\SubscriptionManager\Models\Feature;

class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->slug,
            'metered' => false,
        ];
    }

    public function metered(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'metered' => true,
            ];
        });
    }
}
