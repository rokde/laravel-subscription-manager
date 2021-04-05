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
        $this->table(['code'], Feature::orderBy('code')->get('code')->toArray());
    }
}
