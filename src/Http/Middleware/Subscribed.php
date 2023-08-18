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
     * - resolve a subscribable
     * - does it have the method `subscribed`
     * - check `subscribed` on subscribable
     */
    public function handle(Request $request, Closure $next, ?string $feature = null)
    {
        $subscribable = SubscribableResolver::subscribable();
        if (! $subscribable
            || ! method_exists($subscribable, 'subscribed')
            || ! $subscribable->subscribed($feature)) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
