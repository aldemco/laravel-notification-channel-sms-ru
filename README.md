# Smsru notifications channel for Laravel 7.0+

Here's the latest documentation on Laravel's Notifications System: 

https://laravel.com/docs/master/notifications

## Contents

- [Installation](#installation)
    - [Setting up the SmsRu service](#setting-up-the-SmsRu-service)
- [Usage](#usage)
    - [Available Message methods](#available-methods)
- [Changelog](#changelog)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)


## Installation

Install this package with Composer:

```bash
composer require hanymigame/laravel-notification-channel-sms-ru
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

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use Illuminate\Notifications\Notification;
use NotificationChannels\SmsRu\SmsRuMessage;
use NotificationChannels\SmsRu\SmsRuChannel;

class AccountApproved extends Notification
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
