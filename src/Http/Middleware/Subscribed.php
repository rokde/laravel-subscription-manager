<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rokde\SubscriptionManager\SubscribableResolver;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Subscribed
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param null|string $feature
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $feature = null)
    {
        $user = SubscribableResolver::subscribable();
        if (!$user
            || !method_exists($user, 'subscribed')
            || !$user->subscribed($feature)) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
