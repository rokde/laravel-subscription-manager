# The Subscription Manager for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rokde/laravel-subscription-manager.svg?style=flat-square)](https://packagist.org/packages/rokde/laravel-subscription-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/rokde/laravel-subscription-manager/run-tests?label=tests)](https://github.com/rokde/laravel-subscription-manager/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/rokde/laravel-subscription-manager/Check%20&%20fix%20styling?label=code%20style)](https://github.com/rokde/laravel-subscription-manager/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/rokde/laravel-subscription-manager.svg?style=flat-square)](https://packagist.org/packages/rokde/laravel-subscription-manager)

**NOT READY TO USE IN PRODUCTION ENVIRONMENTS**

# OPEN TODOS

- [ ] Throwing subscription lifecycle events: ended, cycle, ...

- [ ] Metered features: limit numeric usages of a feature: just 10 customers can be managed
    - with limiting on checking subscribed
    - with upgrading possibility
    - with events: quota-reached, quota-exceeded

- [ ] Insights: 
    - partition plans
    - partition features
    - ratio subscribed vs unsubscribed
    - histogram subscriptions
    - average length of subscriptions

The Subscription Manager for Laravel should handle all subscription based stuff without handling any payment. In contrary to the well known payment handling packages like cashier or similar we do not support any payment handling. Just the plans with features, subscribing, starting with a trial and pro-rating or going on an grace period and so on.

For communicating the changes we threw a lot of events and have a toolkit on board including middlewares, blade conditions and other ask-for-feature acceptance services.

What it is not:
- Handling prices
- Handling payments
- Doing a checkout
- Handling coupons or vouchers
- Handling marketing data
- Printing invoices

What it is:
- Assemble Features to plans
- Subscribe a user (or other models) to one or more plans
  - add or remove features to an existing subscription
- Check paid status of a feature (like a guard) for a given user (or other models)
- Display subscription with details

## What it is all about?

### Glossary

**Subscribable** is an Entity which can subscribe to a set of features.
> In the real world it would be a human, which pays money for getting access to something premium.

**Subscription** is the relation between a Subscribable and its limited access granted to a set of features.
> In the real world it would be a contract with a time-limit and a recurrence definition, like a cellphone contract.

**Feature** is a name of a premium area or premium function not every user has access to.
> In the real world you get an access key to a hidden level.

**Plan** is a virtual composition of a set of Features.
> In the real world is a Plan the Package S, M or L with access to a few, more or all functions.

### Definition

On the definition side you have Features - identified by a slug code. Features are there for control. Think of permissions or an access right for a secured part of your application. Features can be virtually grouped by a Plan - but do not have to.

Because we handle no prices, you do not have to have a plan or Feature bundle.

So each Feature can be part in no Plan, one Plan, more Plans or all Plans. A Plan can exist, multiple Plans can exists, but no Plan has to exist. Think of a role in terms of authorization.

### Subscription

A Subscription is loosely coupled to a plan and has a not-linked list of subscribed features. So the loosely coupled Plan is more an informative data link. The list of feature slugs are the important information within the subscription.

A Subscription runs infinite (`ends_at` is null) or until the end date. Additionally you can set a trial period (`trial_ends_at` is not null) end the trial ends at the specified timestamp. Before that you are on a trial.

A Subscription Circle is a virtual object in terms of periods. So a Subscription period is 1 year by default. So each Subscription has a list of Circles, at least one. Each Circle has a maximum of a period length, can be shorter when subscription is cancelled instant.

Subscriptions with an `infinite` period have just on Circle and are not recurring.

[![](https://mermaid.ink/img/eyJjb2RlIjoiZXJEaWFncmFtXG4gICAgICAgICAgRmVhdHVyZSB9by4ub3sgUGxhbiA6IHBsYW5fZmVhdHVyZVxuICAgICAgICAgIFBsYW4gfG8uLm97IFN1YnNjcmlwdGlvbiA6IHN1YnNjcmliZWRfdG9cbiAgICAgICAgICBGZWF0dXJlIHtcbiAgICAgICAgICAgIHN0cmluZyBjb2RlXG4gICAgICAgICAgfVxuICAgICAgICAgIFBsYW4ge1xuICAgICAgICAgICAgc3RyaW5nIG5hbWVcbiAgICAgICAgICB9XG4gICAgICAgICAgU3Vic2NyaXB0aW9uIHtcbiAgICAgICAgICAgIG1vcnBoIHN1YnNjcmliYWJsZVxuICAgICAgICAgICAgYXJyYXlfb2Zfc3RyaW5nIGZlYXR1cmVzXG4gICAgICAgICAgICBzdHJpbmdfb3JfbnVsbCBwZXJpb2RcbiAgICAgICAgICAgIHRpbWVzdGFtcF9vcl9udWxsIHRyaWFsX2VuZHNfYXRcbiAgICAgICAgICAgIHRpbWVzdGFtcF9vcl9udWxsIGVuZHNfYXRcbiAgICAgICAgICB9IiwibWVybWFpZCI6eyJ0aGVtZSI6ImRlZmF1bHQifSwidXBkYXRlRWRpdG9yIjpmYWxzZX0)](https://mermaid-js.github.io/mermaid-live-editor/#/edit/eyJjb2RlIjoiZXJEaWFncmFtXG4gICAgICAgICAgRmVhdHVyZSB9by4ub3sgUGxhbiA6IHBsYW5fZmVhdHVyZVxuICAgICAgICAgIFBsYW4gfG8uLm97IFN1YnNjcmlwdGlvbiA6IHN1YnNjcmliZWRfdG9cbiAgICAgICAgICBGZWF0dXJlIHtcbiAgICAgICAgICAgIHN0cmluZyBjb2RlXG4gICAgICAgICAgfVxuICAgICAgICAgIFBsYW4ge1xuICAgICAgICAgICAgc3RyaW5nIG5hbWVcbiAgICAgICAgICB9XG4gICAgICAgICAgU3Vic2NyaXB0aW9uIHtcbiAgICAgICAgICAgIG1vcnBoIHN1YnNjcmliYWJsZVxuICAgICAgICAgICAgYXJyYXlfb2Zfc3RyaW5nIGZlYXR1cmVzXG4gICAgICAgICAgICBzdHJpbmdfb3JfbnVsbCBwZXJpb2RcbiAgICAgICAgICAgIHRpbWVzdGFtcF9vcl9udWxsIHRyaWFsX2VuZHNfYXRcbiAgICAgICAgICAgIHRpbWVzdGFtcF9vcl9udWxsIGVuZHNfYXRcbiAgICAgICAgICB9IiwibWVybWFpZCI6eyJ0aGVtZSI6ImRlZmF1bHQifSwidXBkYXRlRWRpdG9yIjpmYWxzZX0)


## Installation

You can install the package via composer:

```bash
composer require rokde/laravel-subscription-manager
```

You have to publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Rokde\SubscriptionManager\SubscriptionManagerServiceProvider" --tag="laravel-subscription-manager-migrations"
php artisan migrate --step
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Rokde\SubscriptionManager\SubscriptionManagerServiceProvider" --tag="laravel-subscription-manager-config"
```

You can configure the following parts (documentation is inside the shipped configuration file):
- middleware

## Usage

If your subsciption subscribables are your users, then you have to do the following:

1.) Add the `Subscribable` trait to your User model.

```php
// \App\Models\User
class User extends \Illuminate\Foundation\Auth\User {
    use \Rokde\SubscriptionManager\Models\Concerns\Subscribable;
}
```

2.) Subscribe something

```php
//  using the SubscriptionBuilder, presented by Subscribable trait
/** @var \Rokde\SubscriptionManager\Models\Factory\SubscriptionBuilder $builder */
$builder = $user->newSubscription();    // without any plan
// or
$plan = \Rokde\SubscriptionManager\Models\Plan::byName('Superior');
$builder = $user->newSubscription($plan);    // subscribing to a plan -> the list of features will be taken from the plan
// or
$builder = $user->newFeatureSubscription(['feature-1', 'feature-2']);    // just a list of features, must not exist in database

$builder->periodLength('P1M')   // set period length to a month (default is 1 year)
    ->infinitePeriod()          // or set an infinite period
    ->trialDays(30)             // set 30 days for trial
    ->skipTrial()               // or skip trial (default)
    ->create();                 // and create a subscription
```

3.) Use one of the various checks.

See [Checking chapter](#checking) below.

4.) Ask any information on a subscription

A Subscription can have a relation to a plan. It should have a list of features - but an empty array is good too. You can get an array of Subscription Circles for a Subscription. Each Subscription Circle represents a period within the subscription.

```php
$subscription->circles(); // resolves a list of SubscriptionCircles 
```

Each Subscription Circle has a relation to the subscription, its own start and end date. You can get an interval and the indexed number (1-based) of a circle. It respects the end of a subscription in past or future. The last Subscription Circle on active Subscriptions will have the current timestamp within its borders. So you have to add Circles on your own, when you want to go into the future.

5.) Cancel a subscription

Subscriptions can be cancelled in a few ways, all provided in the `\Rokde\SubscriptionManager\Models\Concerns\HandlesCancellation` trait.

```php
$user->subscription->cancel();              // cancel at the end of the current circle or at the end of the trial when you are on a trial
// or
$user->subscription->cancelNow();           // cancel just right now
// or
$user->subscription->cancelAt($datetime);   // cancel at a concrete time
```

6.) Resume a cancelled subscription

```php
$user->subscription->resume();  // resume a cancelled subscription within grace period
```


### Checking

#### Checking with Middleware

We register a middleware `subscribed` as route middleware. You can change that by publishing config and modify the `subscription-manager::middleware` config key.

You can check route access by just looking if your subscribable has an active subscription by using the middleware like so:

```php
// /routes/web.php
Route::group(['middleware' => 'subscribed'], function () {
    // here you can define your premium routes
});
```

If you want to be a bit more concrete, you can ask if your subscribable has an active subscription to a feature like so:

```php
// /routes/web.php
Route::group(['middleware' => 'subscribed:track-time'], function () {
    // here you can define your routes for all users which have subscribed to a feature "track-time"
});
```

#### Checking on the Subscribable

```php
// @var \App\Models\User|\App\Models\Team $subscribable
//  just currently active subscriptions
$hasAnyActiveSubscription = $subscribable->subscribed();
$isActivelySubscribedToAConcreteFeature = $subscribable->subscribed('feature-1');

//  active and past subscriptions
$hasAnySubscriptionEver = $subscribable->everSubscribed();
$wasSubscribedToAConcreteFeature = $subscribable->everSubscribed('feature-1');
```

#### Getting all subscribed features

You can retrieve an array of all subscribed features.

```php
// @var \App\Models\User|\App\Models\Team $subscribable
$allFeatures = $subscribable->subscribedFeatures(); // ['feature-1', 'feature-2']
```


### Retrieving a different Subscribable

By default a subscribable is always the `Auth::user()`. But you can change that behaviour by registering another closure in your AppServiceProvider.

Maybe you are using JetStream or another team-based subscription subscribable option then do it like so:

```php
// \App\Providers\AppServiceProvider::register()
\Rokde\SubscriptionManager\SubscribableResolver::resolveSubscribable(function () {
    return optional(\Illuminate\Support\Facades\Auth::user())->currentTeam;
});
```

And now you can add the `Subscribable` trait to your Team model:

```php
// \App\Models\Team
class Team extends \Laravel\Jetstream\Team {
    use \Rokde\SubscriptionManager\Models\Concerns\Subscribable;
}
```

Now you can use one of the various checks.

## Events

You can listen on various events during the subscription lifecycle.

The following events gets dispatched:
- `\Rokde\SubscriptionManager\Events\SubscriptionCreated` when a subscription gets created
- `\Rokde\SubscriptionManager\Events\SubscriptionCanceled` when a subscription gets cancelled
- `\Rokde\SubscriptionManager\Events\SubscriptionResumed` when a cancelled subscription gets resumed
- `\Rokde\SubscriptionManager\Events\SubscriptionUpdated` when a subscription gets updated
- `\Rokde\SubscriptionManager\Events\SubscriptionDeleted` when a subscription gets deleted (it is soft deleted)
- `\Rokde\SubscriptionManager\Events\SubscriptionPurged` when a subscription gets finally removed
- `\Rokde\SubscriptionManager\Events\SubscriptionRestored` when a soft-deleted subscription gets restored


## Testing

```bash
composer test
```

## FAQ

**Q** Why don't you use any model-relation between a subscription and the feature models?

**A** The tables for plans and features are just for the selling side of a subscription. You can use this structure yourself by filling these tables with your set of packages and features. But you don't have to to subscribe a user to a set of features.

Your business model can by selling subscriptions on a team level, but you can offer a personal feature to each user, which a user can subscribe to - no matter what the team has features for. So you do not have to struggle if this feature will be already on a plan or in your selling page.

Example: you have an HR app and you can sell a company-wide subscription to give all employees access to your app, billed by the company. A team of users want to track their own private todo list. Now you can subscribe these users to a special feature - like unlocking a special menu item.

**Q** Why don't you handle prices on plans or features or subscriptions?

**A** Because it is hard having prices in a world-wide usable package. You have various prices (with tax, without tax, including fees, various currencies, exchange rates, ...).

Add fields to the shipped migrations (tables) and inherit from the package models to suit your needs for handling price and currency. Sometimes it is easier to display prices just on a marketing page in html, than to have time-related multi-currency prices with or without taxes displayed for personal users or business customers.

Maybe it is better to tell a few USP features on the marketing selling page, but to have atomic features to handle subscription access even for crud operations - like a permission-based app.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Robert Kummer](https://github.com/rokde)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
