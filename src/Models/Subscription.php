<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Rokde\SubscriptionManager\Events\SubscriptionCanceled;
use Rokde\SubscriptionManager\Events\SubscriptionCreated;
use Rokde\SubscriptionManager\Events\SubscriptionDeleted;
use Rokde\SubscriptionManager\Events\SubscriptionPurged;
use Rokde\SubscriptionManager\Events\SubscriptionRestored;
use Rokde\SubscriptionManager\Events\SubscriptionResumed;
use Rokde\SubscriptionManager\Events\SubscriptionUpdated;
use Rokde\SubscriptionManager\Models\Concerns\HandlesCancellation;

/**
 * Class Subscription
 * @package Rokde\SubscriptionManager\Models
 *
 * @property int $id
 * @property string $subscribable_type
 * @property int $subscribable_id
 * @property int|null $plan_id
 * @property string $uuid
 * @property string|null $period
 * @property \Illuminate\Support\Carbon|null $trial_ends_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subscribable
 * @property-read null|\Rokde\SubscriptionManager\Models\Plan $plan
 * @property-read \Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\SubscriptionFeature[] $features
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription active()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription ended()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription notCancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription notOnGracePeriod()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription notOnTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription onGracePeriod()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription onTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|\Rokde\SubscriptionManager\Models\Subscription recurring()
 */
class Subscription extends Model
{
    use HandlesCancellation;
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * @var array
     */
    protected $dispatchesEvents = [
        'cancelled' => SubscriptionCanceled::class,
        'created' => SubscriptionCreated::class,
        'deleted' => SubscriptionDeleted::class,
        'forceDeleted' => SubscriptionPurged::class,
        'restored' => SubscriptionRestored::class,
        'resumed' => SubscriptionResumed::class,
        'updated' => SubscriptionUpdated::class,
    ];

    protected static function booted()
    {
        static::creating(function (Subscription $subscription) {
            if (empty($subscription->uuid)) {
                $subscription->uuid = (string) Str::uuid();
            }
        });

        static::updated(function (Subscription $subscription) {
            //  fire custom events for cancelling or resuming a subscription
            if ($subscription->isDirty('ends_at')) {
                if ($subscription->getAttribute('ends_at') !== null) {
                    $subscription->fireCustomModelEvent('cancelled', 'dispatch');
                } else {
                    $subscription->fireCustomModelEvent('resumed', 'dispatch');
                }
            }
        });
    }

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(SubscriptionFeature::class);
    }

    /**
     * Does the subscription has a plan assigned
     *
     * @param \Rokde\SubscriptionManager\Models\Plan|null $plan
     * @return bool
     */
    public function hasPlan(?Plan $plan = null): bool
    {
        return $plan instanceof Plan
            ? $this->plan_id === $plan->getKey()
            : $this->plan_id !== null;
    }

    /**
     * Does the subscription has a feature assigned
     *
     * @param string|\Rokde\SubscriptionManager\Models\Feature $feature
     * @return bool
     */
    public function hasFeature($feature): bool
    {
        if ($feature instanceof Feature) {
            $feature = $feature->code;
        }

        if ($this->relationLoaded('features')) {
            return $this->features->pluck('code')->contains($feature);
        }

        return $this->features()->pluck('code')->contains($feature);
    }

    /**
     * How long is a normal period on the subscription
     * default: 1 year; infinite period is 1000 years
     *
     * @return \Carbon\CarbonInterval
     * @throws \Exception
     */
    public function periodLength(): CarbonInterval
    {
        return new CarbonInterval($this->period ?? 'P1000Y');
    }

    /**
     * Is the subscription infinite
     *
     * @return bool
     */
    public function isInfinite(): bool
    {
        return $this->period === null;
    }

    /**
     * Is the subscription valid (active or on trial or on grace period, but not ended)
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isActive() || $this->isOnTrial() || $this->isOnGracePeriod();
    }

    /**
     * Is the subscription active (not on grace period or not cancelled)
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->ends_at === null || $this->isOnGracePeriod();
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->whereNull('ends_at')
                ->orWhere(
                    /** @param \Illuminate\Database\Eloquent\Builder|static $query */
                    function (Builder $query) {
                        $query->onGracePeriod();
                    }
                );
        });
    }

    /**
     * Is the subscription recurring (circles) (not infinite, not on trial and not cancelled)
     *
     * @return bool
     */
    public function isRecurring(): bool
    {
        return $this->period !== null && ! $this->isOnTrial() && ! $this->isCancelled();
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeRecurring(Builder $query): void
    {
        $query->notOnTrial()
            ->notCancelled()
            ->whereNotNull('period');
    }

    /**
     * Is subscription cancelled (end date set)
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->ends_at !== null;
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeCancelled(Builder $query): void
    {
        $query->whereNotNull('ends_at');
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeNotCancelled(Builder $query): void
    {
        $query->whereNull('ends_at');
    }

    /**
     * Is subscription already ended (cancelled and not on grace period)
     *
     * @return bool
     */
    public function isEnded(): bool
    {
        return $this->isCancelled() && ! $this->isOnGracePeriod();
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeEnded(Builder $query): void
    {
        $query->cancelled()->notOnGracePeriod();
    }

    /**
     * Is subscription on trial currently
     *
     * @return bool
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeOnTrial(Builder $query)
    {
        $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', Carbon::now()->toDateTimeString());
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeNotOnTrial(Builder $query)
    {
        $query->whereNull('trial_ends_at')
            ->orWhere('trial_ends_at', '<=', Carbon::now()->toDateTimeString());
    }

    /**
     * Is subscription on grace period (end date is set and in future)
     *
     * @return bool
     */
    public function isOnGracePeriod(): bool
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeOnGracePeriod(Builder $query)
    {
        $query->whereNotNull('ends_at')
            ->where('ends_at', '>', Carbon::now()->toDateTimeString());
    }

    /**
     * scope for builder
     *
     * @param \Illuminate\Database\Eloquent\Builder|static $query
     */
    public function scopeNotOnGracePeriod(Builder $query)
    {
        $query->whereNull('ends_at')
            ->orWhere('ends_at', '<=', Carbon::now()->toDateTimeString());
    }

    /**
     * Returns a subscription circles collection
     *
     * @return array|\Rokde\SubscriptionManager\Models\SubscriptionCircle[]
     * @throws \Exception
     */
    public function circles(): array
    {
        $circles = [];

        $startDate = $this->created_at->clone();
        $interval = $this->periodLength();
        $hardEndDate = $this->ends_at;

        do {
            $endDate = $startDate->clone()->add($interval);
            if ($hardEndDate !== null && $endDate->greaterThan($hardEndDate)) {
                $endDate = $hardEndDate->clone();
            }

            $circles[] = new SubscriptionCircle($this, $startDate->clone(), $endDate->clone(), count($circles) + 1);

            //  prepare for next run
            $startDate = $endDate->clone();
        } while (
            ($hardEndDate === null && $endDate->isPast())
            || ($hardEndDate && $hardEndDate->greaterThan($endDate))
        );

        return $circles;
    }
}
