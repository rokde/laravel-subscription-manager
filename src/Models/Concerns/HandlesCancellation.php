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
    /**
     * Cancels a subscription at the end of the current period when not being on grace period already.
     *
     * Infinite subscriptions gets cancelled instantly.
     */
    public function cancel(): self
    {
        if ($this->isOnGracePeriod()) {
            return $this;
        }

        $endsAt = $this->isOnTrial()
            ? $this->trial_ends_at
            : ($this->isInfinite()
                ? Carbon::now()
                : $this->nextPeriod());

        return $this->cancelAt($endsAt);
    }

    /** Cancels the subscription instantly. No matter if it is already on grace period. */
    public function cancelNow(): self
    {
        return $this->cancelAt(Carbon::now());
    }

    /** Cancels the subscription at the given timestamp. No matter if it is already on grace period. */
    public function cancelAt(\DateTimeInterface $endsAt): self
    {
        $this->forceFill([
            'ends_at' => $endsAt,
        ])->save();

        return $this;
    }

    /** Start of the next period begin (or used at end of current cycle) */
    public function nextPeriod(): Carbon
    {
        $endsAt = $this->ends_at !== null
            ? $this->ends_at
            : $this->created_at;

        while ($endsAt->isPast()) {
            $endsAt->add($this->periodLength());
        }

        return $endsAt;
    }

    /** Resumes an already cancelled subscription. Does nothing, when not on grace period. */
    public function resume(): self
    {
        if (! $this->isOnGracePeriod()) {
            return $this;
        }

        $this->forceFill([
            'ends_at' => null,
        ])->save();

        return $this;
    }
}
