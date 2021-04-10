<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Tests;

use Illuminate\Database\Eloquent\Model;
use Rokde\SubscriptionManager\Models\Concerns\Subscribable;

/**
 * Class TestUser
 * @package Rokde\SubscriptionManager\Tests
 *
 * @property int $id
 */
class TestUser extends Model
{
    use Subscribable;

    protected $table = 'test_users';

    protected $guarded = [];
}
