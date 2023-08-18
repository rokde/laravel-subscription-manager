<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Events;

/**
 * Class SubscriptionPurged
 *
 * This event gets thrown when subscription was purged. Purged is the final deletion on soft-deletable.
 *
 * @package Rokde\SubscriptionManager\Events
 */
class SubscriptionPurged
{
    use SubscriptionEvent;
}
