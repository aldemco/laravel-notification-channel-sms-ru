<?php

namespace NotificationChannels\SmsRu\Test;

use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\SmsRu\Exceptions\CouldNotSendNotification;
use NotificationChannels\SmsRu\SmsRuApi;
use NotificationChannels\SmsRu\SmsRuChannel;
use NotificationChannels\SmsRu\SmsRuMessage;
use NotificationChannels\SmsRu\SmsRuNotification;
use RuntimeException;

class SmsRuChannelTest extends TestCase
{
    protected $failedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->failedEvents = [];
        $this->app['events']->listen(NotificationFailed::class, function ($event) {
            $this->failedEvents[] = $event;
        });
    }

    public function testStringNotificationMessageIsConvertedAndSent()
    {
        $notifiable = new SmsRuTestNotifiable('79990000000');
        $notification = new SmsRuTestNotification('Hello');
        $api = \Mockery::mock(SmsRuApi::class);
        $api
            ->shouldReceive('sendMessage')
            ->once()
            ->with('79990000000', \Mockery::on(function ($message) {
                return $message instanceof SmsRuMessage && $message->content === 'Hello';
            }))
            ->andReturn(['status' => 'ok']);

        $result = (new SmsRuChannel($api, $this->app['events']))->send($notifiable, $notification);

        $this->assertSame(['status' => 'ok'], $result);
        $this->assertCount(0, $this->failedEvents);
    }

    public function testSmsRuMessageIsPassedToApi()
    {
        $notifiable = new SmsRuTestNotifiable('79990000000');
        $notificationMessage = SmsRuMessage::create('Hello')->from('Sender');
        $notification = new SmsRuTestNotification($notificationMessage);
        $api = \Mockery::mock(SmsRuApi::class);
        $api
            ->shouldReceive('sendMessage')
            ->once()
            ->with('79990000000', $notificationMessage)
            ->andReturn('sent');

        $result = (new SmsRuChannel($api, $this->app['events']))->send($notifiable, $notification);

        $this->assertSame('sent', $result);
    }

    public function testSmsRuRouteIsPreferredAndPhoneIsFallback()
    {
        $api = \Mockery::mock(SmsRuApi::class);
        $api->shouldReceive('sendMessage')->twice()->andReturn(null);
        $channel = new SmsRuChannel($api, $this->app['events']);
        $notification = new SmsRuTestNotification('Hello');

        $channel->send(new SmsRuTestNotifiable('79990000000', 'route-number'), $notification);
        $channel->send(new SmsRuTestNotifiable('79990000001'), $notification);

        $this->assertCount(0, $this->failedEvents);
        $api->shouldHaveReceived('sendMessage')->with('route-number', \Mockery::type(SmsRuMessage::class))->once();
        $api->shouldHaveReceived('sendMessage')->with('79990000001', \Mockery::type(SmsRuMessage::class))->once();
    }

    public function testInvalidMessageTypeDispatchesNotificationFailed()
    {
        $notifiable = new SmsRuTestNotifiable('79990000000');
        $notification = new SmsRuTestNotification(new \stdClass());
        $api = \Mockery::mock(SmsRuApi::class);
        $api->shouldNotReceive('sendMessage');

        $result = (new SmsRuChannel($api, $this->app['events']))->send($notifiable, $notification);

        $this->assertNull($result);
        $this->assertCount(1, $this->failedEvents);
        $this->assertSame('smsru', $this->failedEvents[0]->channel);
        $this->assertSame($notifiable, $this->failedEvents[0]->notifiable);
        $this->assertSame($notification, $this->failedEvents[0]->notification);
        $this->assertStringContainsString('invalid', strtolower($this->failedEvents[0]->data['message']));
        $this->assertInstanceOf(CouldNotSendNotification::class, $this->failedEvents[0]->data['exception']);
    }

    public function testMissingRecipientDispatchesNotificationFailed()
    {
        $notifiable = new SmsRuTestNotifiable(null);
        $notification = new SmsRuTestNotification('Hello');
        $api = \Mockery::mock(SmsRuApi::class);
        $api->shouldNotReceive('sendMessage');

        $result = (new SmsRuChannel($api, $this->app['events']))->send($notifiable, $notification);

        $this->assertNull($result);
        $this->assertCount(1, $this->failedEvents);
        $this->assertInstanceOf(CouldNotSendNotification::class, $this->failedEvents[0]->data['exception']);
        $this->assertSame('smsru', $this->failedEvents[0]->channel);
    }

    public function testApiExceptionDispatchesNotificationFailed()
    {
        $notifiable = new SmsRuTestNotifiable('79990000000');
        $notification = new SmsRuTestNotification('Hello');
        $exception = new RuntimeException('SMS provider unavailable');
        $api = \Mockery::mock(SmsRuApi::class);
        $api->shouldReceive('sendMessage')->once()->andThrow($exception);

        $result = (new SmsRuChannel($api, $this->app['events']))->send($notifiable, $notification);

        $this->assertNull($result);
        $this->assertCount(1, $this->failedEvents);
        $this->assertSame('SMS provider unavailable', $this->failedEvents[0]->data['message']);
        $this->assertSame($exception, $this->failedEvents[0]->data['exception']);
    }
}

class SmsRuTestNotifiable
{
    public $phone;

    protected $route;

    public function __construct($phone, $route = null)
    {
        $this->phone = $phone;
        $this->route = $route;
    }

    public function routeNotificationFor($channel)
    {
        return $this->route;
    }
}

class SmsRuTestNotification extends Notification implements SmsRuNotification
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function toSmsru($notifiable)
    {
        return $this->message;
    }
}
