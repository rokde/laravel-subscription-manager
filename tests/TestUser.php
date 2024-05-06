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

    private string $token = '';

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

    public function getAuthPasswordName()
    {
        return 'password';
    }
}
