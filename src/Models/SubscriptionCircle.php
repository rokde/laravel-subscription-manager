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

    public function subscription(): Subscription
    {
        return $this->subscription;
    }

    public function start(): Carbon
    {
        return $this->start;
    }

    public function end(): Carbon
    {
        return $this->end;
    }

    public function periodLength(): \DateInterval
    {
        return $this->start->diff($this->end);
    }

    public function intervalString(): string
    {
        return CarbonInterval::getDateIntervalSpec($this->periodLength());
    }

    public function number(): int
    {
        return $this->number;
    }

    public function id(): string
    {
        return $this->subscription->getKey() . '-' . $this->number;
    }

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
