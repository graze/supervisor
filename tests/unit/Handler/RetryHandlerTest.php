<?php

namespace Graze\Supervisor\Handler;

use Graze\Supervisor\Test\TestCase;
use Mockery as m;

class RetryHandlerTest extends TestCase
{
    /** @var int */
    private $max;
    /** @var mixed */
    private $next;
    /** @var mixed */
    private $sup;
    /** @var RetryHandler */
    private $handler;

    public function setUp()
    {
        $this->max = 3;
        $this->next = m::mock('Graze\Supervisor\Handler\HandlerInterface');
        $this->sup = m::mock('Graze\Supervisor\SupervisorInterface');

        $this->handler = new RetryHandler($this->max, $this->next);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Supervisor\Handler\HandlerInterface', $this->handler);
    }

    public function testDecorator()
    {
        $this->assertInstanceOf('Graze\Supervisor\Handler\AbstractDecoratedHandler', $this->handler);
    }

    public function testHandleFailFirstTime()
    {
        $this->sup->shouldReceive('restart')->once();

        $this->assertTrue($this->handler->handleFail(0, $this->sup));
    }

    public function testHandleFailMaxTime()
    {
        $this->sup->shouldReceive('restart')->once();

        $this->assertTrue($this->handler->handleFail($this->max, $this->sup));
    }

    public function testHandleFailMoreThanMaxTimes()
    {
        $this->next->shouldReceive('handleFail')->once()->with(($this->max + 1), $this->sup, null)->andReturn(false);

        $this->assertFalse($this->handler->handleFail(($this->max + 1), $this->sup));
    }

    public function testHandlePassFirstTime()
    {
        $this->next->shouldReceive('handlePass')->once()->with(0, $this->sup)->andReturn(false);

        $this->assertFalse($this->handler->handlePass(0, $this->sup));
    }

    public function testHandlePassMaxTime()
    {
        $this->next->shouldReceive('handlePass')->once()->with($this->max, $this->sup)->andReturn(false);

        $this->assertFalse($this->handler->handlePass($this->max, $this->sup));
    }

    public function testHandlePassMoreThanMaxTimes()
    {
        $this->next->shouldReceive('handlePass')->once()->with(($this->max + 1), $this->sup)->andReturn(false);

        $this->assertFalse($this->handler->handlePass(($this->max + 1), $this->sup));
    }
}
