<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SubscriptionFeature
 * @package Rokde\SubscriptionManager\Models
 *
 * @property int $id
 * @property int $subscription_id
 * @property string $code
 * @property bool $metered
 * @property int|null $quota
 * @property int|null $used
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read int $remaining
 * @property-read \Rokde\SubscriptionManager\Models\Subscription $subscription
 * @property-read \Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\SubscriptionFeatureUsage[] $usages
 */
class SubscriptionFeature extends Model
{
    protected $guarded = [];

    protected $appends = [
        'remaining',
    ];

    protected $casts = [
        'metered' => 'bool',
        'subscription_id' => 'int',
        'quota' => 'int',
        'used' => 'int',
    ];

    public static function fromFeature(Feature $feature): self
    {
        return new static([
            'code' => $feature->code,
            'metered' => $feature->metered,
            'quota' => $feature->metered
                ? optional($feature->pivot)->default_quota + 0
                : null,
            'used' => $feature->metered
                ? 0
                : null,
        ]);
    }

    public static function fromCode(string $code): self
    {
        return new static([
            'code' => $code,
            'metered' => false,
            'quota' => null,
            'used' => null,
        ]);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionFeatureUsage::class);
    }

    public function getRemainingAttribute(): int
    {
        return $this->isMetered()
            ? $this->quota - $this->used
            : 0;
    }

    public function isUsable(): bool
    {
        return !$this->isMetered() || $this->remaining > 0;
    }

    public function isMetered(): bool
    {
        return $this->metered;
    }
}
