<?php

namespace NotificationChannels\SmsRu\Test;

use ArrayObject;
use Illuminate\Container\Container;
use NotificationChannels\SmsRu\SmsRuApi;
use NotificationChannels\SmsRu\SmsRuServiceProvider;

class SmsRuServiceProviderTest extends TestCase
{
    public function testApiIsRegisteredAsASingleton()
    {
        $app = new Container();
        $app->instance('config', new ArrayObject([
            'services.sms_ru.api_id' => 'test-api-id',
        ]));
        $provider = new SmsRuServiceProvider($app);

        $provider->register();

        $firstInstance = $app->make(SmsRuApi::class);
        $secondInstance = $app->make(SmsRuApi::class);

        $this->assertInstanceOf(SmsRuApi::class, $firstInstance);
        $this->assertSame($firstInstance, $secondInstance);
        $this->assertSame([SmsRuApi::class], $provider->provides());
    }
}