<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Feature
 * @package Rokde\SubscriptionManager\Models
 *
 * @property int $id
 * @property string $code
 * @property bool $metered
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Plan[] $plans
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 */
class Feature extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'code',
        'metered',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'metered' => 'boolean',
    ];

    /**
     * Returns a feature by its code
     *
     * @param string $code
     * @return \Rokde\SubscriptionManager\Models\Feature|null
     */
    public static function byCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_feature', 'feature_id')
            ->withPivot('default_quota')
            ->withTimestamps();
    }
}
