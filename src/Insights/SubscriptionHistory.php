<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Insights;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Rokde\SubscriptionManager\Models\Subscription;

class SubscriptionHistory
{
    const PERIOD_DAY = 'day';
    const PERIOD_HOUR = 'hour';
    const PERIOD_MINUTE = 'minute';
    const PERIOD_MONTH = 'month';
    const PERIOD_WEEK = 'week';
    const PERIOD_YEAR = 'year';

    protected \DateTimeInterface $start;
    protected \DateTimeInterface $end;

    protected string $period;

    /**
     * SubscriptionHistory constructor.
     * @param \DateTimeInterface|null $start (last month)
     * @param string $period (day, hour, minute, month, week*, year)
     */
    public function __construct(?\DateTimeInterface $start = null, string $period = self::PERIOD_WEEK)
    {
        $this->start = $start ?? new \DateTime('last month');
        $this->end = new \DateTime();
        $this->period = $period;
    }

    /**
     * set start for history analysing
     *
     * @param \DateTimeInterface $start
     * @return $this
     */
    public function from(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * set end for history analysing
     *
     * @param \DateTimeInterface $end
     * @return $this
     */
    public function until(\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * group partition by
     *
     * @param string $period (day, hour, minute, month, week, year)
     * @return $this
     */
    public function groupBy(string $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * returns histogram data, keyed by a period grouping key
     *
     * @return \Illuminate\Support\Collection|array[]
     */
    public function get(): Collection
    {
        $periods = $this->generatePeriods();
        if ($periods->isEmpty()) {
            return new Collection();
        }

        //  get all Subscriptions
        $subscriptions = Subscription::query()
            ->where('created_at', '>=', $this->start)
            ->where('created_at', '<', $this->end)
            ->get();

        $before = Subscription::query()
            ->where('created_at', '<', $this->start)
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $this->start);
            })
            ->get();
        $offset = $before->count();

        $offsetTrial = $before->filter(function (Subscription $subscription) {
            return $subscription->trial_ends_at !== null
                && $subscription->trial_ends_at->gte($this->start);
        })->count();

        return $periods->mapWithKeys(function (array $periodBoundaries) use ($subscriptions, &$offset, &$offsetTrial) {
            [$periodStart, $periodEnd, $periodKey] = $periodBoundaries;

            $subscriptionsInPeriod = $subscriptions->filter(function (Subscription $subscription) use ($periodStart, $periodEnd) {
                return $subscription->created_at->lt($periodEnd);
            });

            $createdInPeriod = $subscriptionsInPeriod->filter(function (Subscription $subscription) use ($periodStart, $periodEnd) {
                return $subscription->created_at->gte($periodStart);
            })->count();

            $onTrialInPeriod = $subscriptionsInPeriod->filter(function (Subscription $subscription) use ($periodStart, $periodEnd) {
                return $subscription->trial_ends_at !== null
                    && ($subscription->trial_ends_at->gte($periodEnd)
                        || ($subscription->trial_ends_at->gte($periodStart) && $subscription->trial_ends_at->lt($periodEnd)));
            })->count() + $offsetTrial;
            $offsetTrial = 0;

            $onGracePeriodInPeriod = $subscriptionsInPeriod->filter(function (Subscription $subscription) use ($periodStart, $periodEnd) {
                return $subscription->ends_at !== null
                    && ($subscription->ends_at->gte($periodEnd)
                        || ($subscription->ends_at->gte($periodStart) && $subscription->ends_at->lt($periodEnd)));
            })->count();

            $endedInPeriod = $subscriptionsInPeriod->filter(function (Subscription $subscription) use ($periodStart, $periodEnd) {
                return $subscription->ends_at !== null
                    && $subscription->ends_at->gte($periodStart) && $subscription->ends_at->lt($periodEnd);
            })->count();

            $countInPeriod = $offset + $createdInPeriod;
            $offset = $countInPeriod - $endedInPeriod;

            return [
                $periodKey => [
                    'start' => $periodStart->toDateTimeString(),
                    'end' => $periodEnd->toDateTimeString(),
                    'count' => $countInPeriod,
                    'new' => $createdInPeriod,
                    'trial' => $onTrialInPeriod,
                    'grace' => $onGracePeriodInPeriod,
                    'ended' => $endedInPeriod,
                ],
            ];
        });
    }

    /**
     * generates a period collection of dates between start and end (including dates)
     *
     * @return \Illuminate\Support\Collection
     * Thanks to the great work of Spatie!
     * Code majorly copied from https://github.com/spatie/laravel-stats/blob/master/src/StatsQuery.php
     */
    private function generatePeriods(): Collection
    {
        $periods = new Collection();
        $startOfPeriods = (new Carbon($this->start))->startOf($this->period);

        do {
            $periods->push([
                $startOfPeriods->copy(),
                $startOfPeriods->copy()->add(1, $this->period),
                $startOfPeriods->format($this->getPeriodTimestampFormat()),
            ]);

            $startOfPeriods->add(1, $this->period);
        } while ($startOfPeriods->lt($this->end));

        return $periods;
    }

    /**
     * return period-base timestamp format, used for key of histogram collection
     *
     * @return string
     * Thanks to the great work of Spatie!
     * Code majorly copied from https://github.com/spatie/laravel-stats/blob/master/src/StatsQuery.php
     */
    private function getPeriodTimestampFormat(): string
    {
        switch ($this->period) {
            case static::PERIOD_DAY:
                return 'Y-m-d';
            case static::PERIOD_HOUR:
                return 'Y-m-d H';
            case static::PERIOD_MINUTE:
                return 'Y-m-d H:i';
            case static::PERIOD_MONTH:
                return 'Y-m';
            case static::PERIOD_WEEK:
                // see https://stackoverflow.com/questions/15562270/php-datew-vs-mysql-yearweeknow
                return 'oW';
            case static::PERIOD_YEAR:
                return 'Y';
        }

        return 'oW';
    }
}
