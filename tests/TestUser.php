<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Concerns\Subscribable;

/**
 * Class TestUser
 * @package Rokde\SubscriptionManager\Tests
 *
 * @property int $id
 */
class TestUser extends Model implements Authenticatable
{
    use Subscribable;

    protected $table = 'test_users';

    protected $guarded = [];

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return 'password';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // TODO: Implement setRememberToken() method.
    }

    public function getRememberTokenName()
    {
        return 'remember_me';
    }
}
