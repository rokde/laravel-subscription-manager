<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * Class SubscriptionFeatureUsage
 * @package Rokde\SubscriptionManager\Models
 *
 * @property int $id
 * @property int $subscription_feature_id
 * @property string $used_by_type
 * @property int $used_by_id
 * @property int|null $used
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read \Rokde\SubscriptionManager\Models\SubscriptionFeature $subscriptionFeature
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $usedBy
 */
class SubscriptionFeatureUsage extends Model
{
    protected $casts = [
        'used' => 'int',
    ];

    protected $guarded = [];

    public function usedBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function subscriptionFeature(): BelongsTo
    {
        return $this->belongsTo(SubscriptionFeature::class);
    }
}
