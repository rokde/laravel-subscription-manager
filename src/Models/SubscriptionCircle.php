<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class SubscriptionCircle
{
    private Subscription $subscription;
    private Carbon $start;
    private Carbon $end;
    private int $number;

    public function __construct(Subscription $subscription, Carbon $start, Carbon $end, int $number)
    {
        $this->subscription = $subscription;
        $this->start = $start;
        $this->end = $end;
        $this->number = $number;
    }

    /** Reference to subscription */
    public function subscription(): Subscription
    {
        return $this->subscription;
    }

    /** Start of the subscription circle */
    public function start(): Carbon
    {
        return $this->start;
    }

    /** End of the subscription circle */
    public function end(): Carbon
    {
        return $this->end;
    }

    /** Period length of the subscription circle */
    public function periodLength(): \DateInterval
    {
        return $this->start->diff($this->end);
    }

    /** Returns interval string for current subscription circle */
    public function intervalString(): string
    {
        return CarbonInterval::getDateIntervalSpec($this->periodLength());
    }

    /** Returns number of the subscription circle within the subscription */
    public function number(): int
    {
        return $this->number;
    }

    /** Get a unique number for subscription circle within the subscription  */
    public function id(): string
    {
        return $this->subscription->getKey() . '-' . $this->number;
    }

    /** Array representation of a subscription circle  */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'subscription_id' => $this->subscription->getKey(),
            'number' => $this->number,
            'start' => $this->start->toDateTimeString(),
            'end' => $this->end->toDateTimeString(),
            'interval' => $this->intervalString(),
        ];
    }
}
