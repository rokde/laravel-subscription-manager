# This is my package SubscriptionManager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rokde/laravel_subscription_manager.svg?style=flat-square)](https://packagist.org/packages/rokde/laravel_subscription_manager)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/rokde/laravel_subscription_manager/run-tests?label=tests)](https://github.com/rokde/laravel_subscription_manager/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/rokde/laravel_subscription_manager/Check%20&%20fix%20styling?label=code%20style)](https://github.com/rokde/laravel_subscription_manager/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/rokde/laravel_subscription_manager.svg?style=flat-square)](https://packagist.org/packages/rokde/laravel_subscription_manager)

The Laravel Subscription Manager should handle all subscription based stuff without handling any payment. In contrary to the well known payment handling packages like cashier or similar we do not support any payment handling. Just the plans with features, subscribing, starting with a trial and pro-rating or going on an grace period and so on.

For communicating the changes we threw a lot of events and have a toolkit on board including middlewares, blade conditions and other ask-for-feature acceptance services.

What it is not:
- Handling prices
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

## Installation

You can install the package via composer:

```bash
composer require rokde/laravel-subscription-manager
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Rokde\SubscriptionManager\SubscriptionManagerServiceProvider" --tag="laravel_subscription_manager-migrations"
php artisan migrate --step
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Rokde\SubscriptionManager\SubscriptionManagerServiceProvider" --tag="laravel-subscription-manager-config"
```

## Usage

```php
// coming soon...
```

## Testing

```bash
composer test
```

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
