<?php
namespace Graze\Supervisor\Handler;

use Exception;
use Mockery as m;

class RetryHandlerTest extends \PHPUnit_Framework_TestCase
{
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
        $this->assertInstanceOf('Graze\Supervisor\Handler\DecoratedHandler', $this->handler);
    }

    public function testHandleFailFirstTime()
    {
        $this->sup->shouldReceive('restart')->once()->withNoArgs();

        $this->handler->handleFail(0, $this->sup);
    }

    public function testHandleFailMaxTime()
    {
        $this->sup->shouldReceive('restart')->once()->withNoArgs();

        $this->handler->handleFail($this->max, $this->sup);
    }

    public function testHandleFailMoreThanMaxTimes()
    {
        $this->next->shouldReceive('handleFail')->once()->with(($this->max + 1), $this->sup, null);

        $this->handler->handleFail(($this->max + 1), $this->sup);
    }

    public function testHandlePassFirstTime()
    {
        $this->next->shouldReceive('handlePass')->once()->with(0, $this->sup);

        $this->handler->handlePass(0, $this->sup);
    }

    public function testHandlePassMaxTime()
    {
        $this->next->shouldReceive('handlePass')->once()->with($this->max, $this->sup);

        $this->handler->handlePass($this->max, $this->sup);
    }

    public function testHandlePassMoreThanMaxTimes()
    {
        $this->next->shouldReceive('handlePass')->once()->with(($this->max + 1), $this->sup);

        $this->handler->handlePass(($this->max + 1), $this->sup);
    }
}
