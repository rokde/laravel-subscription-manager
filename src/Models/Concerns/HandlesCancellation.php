<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models\Concerns;

use Illuminate\Support\Carbon;
use Rokde\SubscriptionManager\Models\Subscription;

/**
 * Trait HandlesCancellation
 * @package Rokde\SubscriptionManager\Models\Concerns
 *
 * @property Subscription $this
 */
trait HandlesCancellation
{
    public function cancel(): self
    {
        if ($this->onGracePeriod()) {
            return $this;
        }

        $endsAt = $this->onTrial()
            ? $this->trial_ends_at
            : $this->nextPeriod();

        return $this->cancelAt($endsAt);
    }

    public function cancelNow(): self
    {
        return $this->cancelAt(Carbon::now());
    }

    public function cancelAt(\DateTimeInterface $endsAt): self
    {
        $this->forceFill([
            'ends_at' => $endsAt,
        ])->save();

        return $this;
    }

    public function nextPeriod(): Carbon
    {
        /** @var Carbon $endsAt */
        $endsAt = $this->ends_at !== null
            ? $this->ends_at
            : $this->created_at;

        while ($endsAt->isPast()) {
            $endsAt->add($this->periodLength());
        }

        return $endsAt;
    }
}
