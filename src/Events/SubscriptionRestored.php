<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Events;

/**
 * Class SubscriptionRestored
 *
 * This event gets thrown when subscription was restored or undeleted.
 *
 * @package Rokde\SubscriptionManager\Events
 */
class SubscriptionRestored
{
    use SubscriptionEvent;
}
