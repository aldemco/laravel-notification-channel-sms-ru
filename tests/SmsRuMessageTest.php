<?php

namespace NotificationChannels\SmsRu\Test;

use NotificationChannels\SmsRu\SmsRuMessage;

class SmsRuMessageTest extends TestCase
{
    public function testCreateAndConstructorSetContent()
    {
        $this->assertSame('Hello', SmsRuMessage::create('Hello')->content);
        $this->assertSame('World', (new SmsRuMessage('World'))->content);
    }

    public function testMessageOptionsCanBeSetFluently()
    {
        $message = SmsRuMessage::create()
            ->content('Hello')
            ->from('Sender')
            ->time(1234567890)
            ->test(true)
            ->translit(true)
            ->partnerId('partner-1');

        $this->assertSame('Hello', $message->content);
        $this->assertSame('Sender', $message->from);
        $this->assertSame(1234567890, $message->time);
        $this->assertTrue($message->test);
        $this->assertTrue($message->translit);
        $this->assertSame('partner-1', $message->partnerId);
    }

    public function testBooleanOptionsCanBeDisabled()
    {
        $message = SmsRuMessage::create()->test(false)->translit(false);

        $this->assertFalse($message->test);
        $this->assertFalse($message->translit);
    }
}