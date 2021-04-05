<?php

namespace Rokde\SubscriptionManager\Commands;

use Illuminate\Console\Command;
use Rokde\SubscriptionManager\Models\Feature;

class FeaturesListCommand extends Command
{
    public $signature = 'features:list';

    public $description = 'List all features';

    public function handle()
    {
        $rows = Feature::orderBy('code')
            ->get()
            ->map(function (Feature $feature) {
                return [
                    $feature->code,
                    $feature->plans->sortBy('name')->pluck('name')->implode(', '),
                ];
            });

        $this->table(['feature', 'plans'], $rows->toArray());
    }
}
