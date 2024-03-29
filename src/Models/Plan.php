<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Plan
 * @package Rokde\SubscriptionManager\Models
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\Feature[] $features
 * @property-read \Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\Feature[] $meteredFeatures
 * @property-read \Illuminate\Database\Eloquent\Collection|\Rokde\SubscriptionManager\Models\Subscription[] $subscriptions
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 */
class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /** Returns a plan by name */
    public static function byName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_feature', 'plan_id')
            ->withPivot('default_quota')
            ->withTimestamps();
    }

    public function meteredFeatures(): BelongsToMany
    {
        return $this->features()
            ->wherePivotNotNull('default_quota');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
