<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests\Http\Middleware;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Rokde\SubscriptionManager\Http\Middleware\Subscribed;
use Rokde\SubscriptionManager\Models\Concerns\Subscribable;
use Rokde\SubscriptionManager\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SubscribedMiddlewareTest extends TestCase
{
    /** @test */
    public function it_fails_when_there_is_no_current_user()
    {
        $request = new Request();

        $this->expectException(AccessDeniedHttpException::class);

        (new Subscribed())->handle($request, function ($request) {
            $this->assertNull($request->user());
        });
    }

    /** @test */
    public function it_fails_when_user_is_no_subscribable()
    {
        $user = new class implements Authenticatable {
            private $token = null;

            public function getAuthIdentifierName()
            {
                return 'user';
            }

            public function getAuthIdentifier()
            {
                return 1;
            }

            public function getAuthPassword()
            {
                return 'password';
            }

            public function getRememberToken()
            {
                return $this->token;
            }

            public function setRememberToken($value)
            {
                $this->token = $value;
            }

            public function getRememberTokenName()
            {
                return 'remember_me';
            }
        };

        $this->actingAs($user);

        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $this->expectException(AccessDeniedHttpException::class);

        (new Subscribed())->handle($request, function ($request) {
            $this->assertNull($request->user());
        });
    }

    /** @test */
    public function it_fails_when_user_has_no_subscription()
    {
        $user = new class extends User {
            use Subscribable;
        };

        $this->actingAs($user);

        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $this->expectException(AccessDeniedHttpException::class);

        (new Subscribed())->handle($request, function ($request) {
            $this->assertNotNull($request->user());
        });
    }

    /** @-test */
    public function it_succeeds_when_user_has_subscription()
    {
        $user = new class extends User {
            use Subscribable;

            public $id = 1;

            protected function setForeignAttributesForCreate(Model $model)
            {
                $model->setAttribute('subscribable_id', 1);
            }
        };

        $user->newSubscription()->create();

        $this->actingAs($user);

        $request = new Request();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        (new Subscribed())->handle($request, function ($request) {
            $this->assertNotNull($request->user());
        });
    }
}
