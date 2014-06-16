<?php
namespace Graze\Supervisor\Handler;

use Exception;
use Mockery as m;

class UnexpectedTerminationHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->next = m::mock('Graze\Supervisor\Handler\HandlerInterface');
        $this->sup = m::mock('Graze\Supervisor\SupervisorInterface');

        $this->handler = new UnexpectedTerminationHandler($this->next);
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
        $this->setExpectedException('Graze\Supervisor\Exception\UnexpectedTerminationException');

        $this->handler->handleFail(0, $this->sup, $exception);
    }

    public function testHandleFailWithoutException()
    {
        $this->setExpectedException('Graze\Supervisor\Exception\UnexpectedTerminationException');

        $this->handler->handleFail(0, $this->sup);
    }

    public function testHandlePass()
    {
        $this->next->shouldReceive('handlePass')->once()->with(0, $this->sup);

        $this->handler->handlePass(0, $this->sup);
    }
}
