<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Events;

/**
 * Class SubscriptionDeleted
 *
 * This event gets thrown when subscription was deleted. A subscription is soft-deletable.
 *
 * @package Rokde\SubscriptionManager\Events
 */
class SubscriptionDeleted
{
    use SubscriptionEvent;
}
