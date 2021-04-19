<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager;

use Closure;
use Illuminate\Support\Facades\Auth;

class SubscribableResolver
{
    private static ?Closure $subscribableResolver = null;

    /**
     * Resolve the subscribable with a custom closure
     *
     * @param \Closure|null $callback
     */
    public static function resolveSubscribable(Closure $callback = null): void
    {
        static::$subscribableResolver = $callback;
    }

    /**
     * Returns the subscribable when authenticated
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model|\Rokde\SubscriptionManager\Models\Concerns\Subscribable|null
     */
    public static function subscribable()
    {
        return call_user_func(static::$subscribableResolver ?: function () {
            return Auth::user();
        });
    }
}
