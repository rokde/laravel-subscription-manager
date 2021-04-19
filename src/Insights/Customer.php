<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Insights;

use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Rokde\SubscriptionManager\Models\Subscription;

class Customer
{
    /**
     * returns all customers - distinct list of subscribers
     *
     * @param Carbon\CarbonPeriod|null $period
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model[]
     */
    public function get(?CarbonPeriod $period = null): Collection
    {
        $query = Subscription::query();

        $query->when($period !== null, function (Builder $query) use ($period) {
            $query->where('created_at', '<=', $period->getEndDate()->toDateTimeString())
                ->where(function (Builder $query) use ($period) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $period->getStartDate()->toDateTimeString());
                })
                ->get(['subscribable_type', 'subscribable_id']);
        });

        return $query->get(['subscribable_type', 'subscribable_id'])
            ->unique(function (Subscription $subscription) {
                return $subscription->subscribable_type . '-' . $subscription->subscribable_id;
            })
            ->map(function (Subscription $subscription) {
                return $subscription->subscribable;
            })
            ->values();
    }

    /**
     * returns all churning customers - distinct list of subscribers
     *
     * @param \Carbon\CarbonPeriod $period
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function churnCustomers(CarbonPeriod $period): Collection
    {
        return Subscription::query()
            ->where('ends_at', '<=', $period->getEndDate()->toDateTimeString())
            ->where('ends_at', '>=', $period->getStartDate()->toDateTimeString())
            ->get(['subscribable_type', 'subscribable_id'])
            ->unique(function (Subscription $subscription) {
                return $subscription->subscribable_type . '-' . $subscription->subscribable_id;
            })
            ->map(function (Subscription $subscription) {
                return $subscription->subscribable;
            })
            ->values();
    }
}
