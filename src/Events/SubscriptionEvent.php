<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Rokde\SubscriptionManager\Models\Subscription;

trait SubscriptionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }
}
