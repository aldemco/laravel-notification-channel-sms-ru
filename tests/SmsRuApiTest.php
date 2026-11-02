<?php

namespace NotificationChannels\SmsRu\Test;

use NotificationChannels\SmsRu\SmsRuApi;
use NotificationChannels\SmsRu\SmsRuMessage;
use Zelenin\SmsRu\Auth\ApiIdAuth;
use Zelenin\SmsRu\Client\ClientInterface;
use Zelenin\SmsRu\Response\SmsResponse;
use Mockery;

class SmsRuApiTest extends TestCase
{
    public function testMessageOptionsAreConvertedToSmsRuApiParameters()
    {
        $time = time() + 3600;
        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('request')
            ->once()
            ->with('sms/send', Mockery::on(function ($parameters) use ($time) {
                $expected = [
                    'to' => '79990000000',
                    'text' => 'Hello',
                    'from' => 'Sender',
                    'time' => $time,
                    'translit' => 1,
                    'test' => 1,
                    'partner_id' => 'partner-1',
                    'api_id' => 'test-api-id',
                ];

                return $parameters === $expected;
            }))
            ->andReturn("100\nsms-id");

        $api = new SmsRuApi(new ApiIdAuth('test-api-id'), $client);
        $response = $api->sendMessage('79990000000', SmsRuMessage::create('Hello')
            ->from('Sender')
            ->time($time)
            ->translit(true)
            ->test(true)
            ->partnerId('partner-1'));

        $this->assertInstanceOf(SmsResponse::class, $response);
        $this->assertSame('100', $response->code);
        $this->assertSame(['sms-id'], $response->ids);
    }

    public function testUnsetOptionalMessageParametersAreNotSent()
    {
        $client = Mockery::mock(ClientInterface::class);
        $client
            ->shouldReceive('request')
            ->once()
            ->with('sms/send', [
                'to' => '79990000000',
                'text' => 'Hello',
                'api_id' => 'test-api-id',
            ])
            ->andReturn('100');

        $api = new SmsRuApi(new ApiIdAuth('test-api-id'), $client);

        $response = $api->sendMessage('79990000000', new SmsRuMessage('Hello'));

        $this->assertInstanceOf(SmsResponse::class, $response);
        $this->assertSame('100', $response->code);
    }
}
