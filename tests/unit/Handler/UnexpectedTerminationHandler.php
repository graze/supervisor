<?php
namespace Graze\Supervisor\Handler;

use Exception;
use Mockery as m;
use Graze\Supervisor\Test\TestCase;

class UnexpectedTerminationHandlerTest extends TestCase
{
    /** @var mixed */
    private $next;
    /** @var mixed */
    private $sup;
    /** @var UnexpectedTerminationHandler */
    private $handler;

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
        $this->assertInstanceOf('Graze\Supervisor\Handler\AbstractDecoratedHandler', $this->handler);
    }

    /**
     * @expectedException \Graze\Supervisor\Exception\UnexpectedTerminationException
     */
    public function testHandleFailWithException()
    {
        $exception = new Exception('foo');

        $this->handler->handleFail(0, $this->sup, $exception);
    }

    /**
     * @expectedException \Graze\Supervisor\Exception\UnexpectedTerminationException
     */
    public function testHandleFailWithoutException()
    {
        $this->handler->handleFail(0, $this->sup);
    }

    public function testHandlePass()
    {
        $this->next->shouldReceive('handlePass')->once()->with(0, $this->sup)->andReturn(false);

        $this->assertFalse($this->handler->handlePass(0, $this->sup));
    }
}
