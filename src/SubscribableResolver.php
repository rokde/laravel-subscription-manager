<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager;

use Illuminate\Support\Facades\Auth;

class SubscribableResolver
{
    private static $subscribableResolver = null;

    public static function resolveSubscribable(\callable $callback = null): void
    {
        static::$subscribableResolver = $callback;
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model|\Rokde\SubscriptionManager\Models\Concerns\Subscribable
     */
    public static function subscribable()
    {
        return call_user_func(static::$subscribableResolver ?: function () {
            return Auth::user();
        });
    }
}
