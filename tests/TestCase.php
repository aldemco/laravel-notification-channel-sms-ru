<?php

namespace NotificationChannels\SmsRu\Test;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Container();
        Container::setInstance($this->app);
        $this->app->instance('events', new Dispatcher($this->app));
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        Container::setInstance(null);

        parent::tearDown();
    }
}