<?php declare(strict_types=1);

namespace Rokde\SubscriptionManager\Events;

/**
 * Class SubscriptionResumed
 *
 * This event gets thrown when subscription was resumed. This could only be done with cancelled subscriptions.
 *
 * @package Rokde\SubscriptionManager\Events
 */
class SubscriptionResumed
{
    use SubscriptionEvent;
}
