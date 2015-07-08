<?php
namespace Graze\Supervisor\Handler;

use Exception;
use Mockery as m;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
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
        $this->assertInstanceOf('Graze\Supervisor\Handler\DecoratedHandler', $this->handler);
    }

    public function testHandleFailWithException()
    {
        $exception = new Exception('foo');
        $this->setExpectedException(get_class($exception));

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
