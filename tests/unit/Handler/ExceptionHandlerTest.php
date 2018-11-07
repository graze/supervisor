<?php

namespace Graze\Supervisor\Handler;

use Exception;
use Graze\Supervisor\Test\TestCase;
use Mockery as m;

class ExceptionHandlerTest extends TestCase
{
    /** @var mixed */
    private $next;
    /** @var mixed */
    private $sup;
    /** @var ExceptionHandler */
    private $handler;

    public function setUp()
    {
        $this->next = m::mock('Graze\Supervisor\Handler\HandlerInterface');
        $this->sup = m::mock('Graze\Supervisor\SupervisorInterface');

        $this->handler = new ExceptionHandler($this->next);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Supervisor\Handler\HandlerInterface', $this->handler);
    }

    public function testDecorator()
    {
        $this->assertInstanceOf('Graze\Supervisor\Handler\AbstractDecoratedHandler', $this->handler);
    }

    /**
     * @expectedException \Exception
     */
    public function testHandleFailWithException()
    {
        $exception = new Exception('foo');

        $this->handler->handleFail(0, $this->sup, $exception);
    }

    public function testHandleFailWithoutException()
    {
        $this->next->shouldReceive('handleFail')->once()->with(0, $this->sup, null)->andReturn(false);

        $this->assertFalse($this->handler->handleFail(0, $this->sup));
    }

    public function testHandlePass()
    {
        $this->next->shouldReceive('handlePass')->once()->with(0, $this->sup)->andReturn(false);

        $this->assertFalse($this->handler->handlePass(0, $this->sup));
    }
}
