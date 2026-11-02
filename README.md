# Smsru notifications channel for Laravel 7.0–13.x

Here's the latest documentation on Laravel's Notifications System: 

https://laravel.com/docs/master/notifications

## Contents

- [Upgrade note: SmsRuNotification interface](#upgrade-note-smsrunotification-interface)
- [Installation](#installation)
    - [Setting up the SmsRu service](#setting-up-the-SmsRu-service)
- [Usage](#usage)
    - [Available Message methods](#available-methods)
- [Changelog](#changelog)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Upgrade note: `SmsRuNotification` interface

When updating to a package version that includes the `SmsRuNotification` interface, update each notification that uses the SMS.ru channel to implement it. This makes the `toSmsru()` contract explicit and prevents Intelephense from reporting the method as undefined. The notification must still provide its `toSmsru()` method and may return either a string or an `SmsRuMessage`.

Add the interface import and implement it alongside Laravel's base `Notification` class:

```php app/Notifications/AccountApproved.php
use Illuminate\Notifications\Notification;
use NotificationChannels\SmsRu\SmsRuNotification;

class AccountApproved extends Notification implements SmsRuNotification
{
    // Keep your existing toSmsru($notifiable) method.
}
```

## Installation

Install this package with Composer:

```bash
composer require aldemco/laravel-notification-channel-sms-ru:dev-main
```

The service provider gets loaded automatically. Or you can do this manually:
```php
// config/app.php
'providers' => [
    ...
    NotificationChannels\SmsRu\SmsRuServiceProvider::class,
],
```

### Setting up the SmsRu service

Add your SmsRu apiID, default sender name (or phone number) to your `config/services.php`:

```php
// config/services.php
...
'sms_ru' => [
    'api_id'  => env('SMSRU_API_ID'),
],
...
```

## Local development with Docker

The Docker environment includes PHP 8.4, Composer, and Xdebug. Start the container and install dependencies:

```sh
docker compose up -d --build
docker compose exec app composer update
docker compose exec app composer test
```

The project directory is mounted at `/app`; Composer installs dependencies into the project’s `vendor` directory. The container runs as UID/GID 1000 by default. If your host user has different IDs, set `UID` and `GID` in the environment before starting Compose. To run a command in a disposable container instead, use `docker compose run --rm app composer test` after installing dependencies.

Xdebug listens for IDE connections on port `9003`. Configure your IDE to accept connections from `host.docker.internal` and map the project root to `/app`. Stop the container with `docker compose down`.

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use Illuminate\Notifications\Notification;
use NotificationChannels\SmsRu\SmsRuMessage;
use NotificationChannels\SmsRu\SmsRuChannel;
use NotificationChannels\SmsRu\SmsRuNotification;

class AccountApproved extends Notification implements SmsRuNotification
{
    public function via($notifiable)
    {
        return [SmsRuChannel::class];
    }

    public function toSmsru($notifiable)
    {
        return (new SmsRuMessage())->content("Hello SMS!!!")->test(true)->translit(false);
    }
}
```

In your notifiable model, make sure to include a `routeNotificationForSmsru()` method, which returns a phone number
or an array of phone numbers.

```php
public function routeNotificationForSmsru()
{
    return $this->phone;
}
```

### Available methods

`from()`: Sets the sender's name or phone number.

`content()`: Set a content of the notification message.

`time()`: Example argument = `time() + 7*60*60` - Postpone shipping for 7 hours.

`translit()`: Text transliteration

`test()`: Test SMS sending (free)

`from()`: Approved letter sender

`parentId()`: You can specify your partner ID if you integrate the code into a foreign system


## Credits

- [fomvasss](https://github.com/fomvasss)
- [zelenin/sms_ru](https://github.com/zelenin/sms_ru)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
