# This is my package SubscriptionManager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rokde/laravel_subscription_manager.svg?style=flat-square)](https://packagist.org/packages/rokde/laravel_subscription_manager)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/rokde/laravel_subscription_manager/run-tests?label=tests)](https://github.com/rokde/laravel_subscription_manager/actions?query=workflow%3ATests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/rokde/laravel_subscription_manager/Check%20&%20fix%20styling?label=code%20style)](https://github.com/rokde/laravel_subscription_manager/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/rokde/laravel_subscription_manager.svg?style=flat-square)](https://packagist.org/packages/rokde/laravel_subscription_manager)

[](delete) 1) manually replace `Robert Kummer, rokde, auhor@domain.com, rokde, rokde, Vendor Name, laravel-subscription-manager, laravel_subscription_manager, laravel_subscription_manager, SubscriptionManager, This is my package SubscriptionManager` with their correct values
[](delete) in `CHANGELOG.md, LICENSE.md, README.md, ExampleTest.php, ModelFactory.php, SubscriptionManager.php, SubscriptionManagerCommand.php, SubscriptionManagerFacade.php, SubscriptionManagerServiceProvider.php, TestCase.php, composer.json, create_laravel_subscription_manager_table.php.stub`
[](delete) and delete `configure-laravel_subscription_manager.sh`

[](delete) 2) You can also run `./configure-laravel_subscription_manager.sh` to do this automatically.

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/package-laravel_subscription_manager-laravel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/package-laravel_subscription_manager-laravel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require rokde/laravel_subscription_manager
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Rokde\SubscriptionManager\SubscriptionManagerServiceProvider" --tag="laravel_subscription_manager-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Rokde\SubscriptionManager\SubscriptionManagerServiceProvider" --tag="laravel_subscription_manager-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$laravel_subscription_manager = new Rokde\SubscriptionManager();
echo $laravel_subscription_manager->echoPhrase('Hello, Spatie!');
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