<?php
namespace Graze\Supervisor;

use Exception;
use Mockery as m;

class SupervisorSupervisorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->handler = m::mock('Graze\Supervisor\Handler\HandlerInterface');
        $this->supA = $a = m::mock('Graze\Supervisor\SupervisorSupervisor');
        $this->supB = $b = m::mock('Graze\Supervisor\SupervisorSupervisor');
        $this->supC = $c = m::mock('Graze\Supervisor\SupervisorSupervisor');
        $this->sups = [$a, $b, $c];

        $this->sup = new SupervisorSupervisor($this->sups, $this->handler);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Supervisor\SupervisorInterface', $this->sup);
    }

    public function testIsRunningAllTrue()
    {
        $this->supA->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(true);
        $this->supC->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(true);

        $this->assertTrue($this->sup->isRunning());
    }

    public function testIsRunningSomeTrue()
    {
        $this->supA->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(false);
        $this->supB->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(true);
        $this->supC->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(false);

        $this->assertTrue($this->sup->isRunning());
    }

    public function testIsRunningAllFalse()
    {
        $this->supA->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(false);
        $this->supB->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('isRunning')->once()->withNoArgs()->andReturn(false);

        $this->assertFalse($this->sup->isRunning());
    }

    public function testIsSuccessfulAllTrue()
    {
        $this->supA->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(true);
        $this->supC->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(true);

        $this->assertTrue($this->sup->isSuccessful());
    }

    public function testIsSuccessfulSomeTrue()
    {
        $this->supA->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(true);

        $this->assertFalse($this->sup->isSuccessful());
    }

    public function testIsSuccessfulAllFalse()
    {
        $this->supA->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(false);
        $this->supB->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('isSuccessful')->once()->withNoArgs()->andReturn(false);

        $this->assertFalse($this->sup->isSuccessful());
    }

    public function testIsTerminatedAllTrue()
    {
        $this->supA->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(true);
        $this->supC->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(true);

        $this->assertTrue($this->sup->isTerminated());
    }

    public function testIsTerminatedSomeTrue()
    {
        $this->supA->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(true);

        $this->assertFalse($this->sup->isTerminated());
    }

    public function testIsTerminatedAllFalse()
    {
        $this->supA->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(false);
        $this->supB->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('isTerminated')->once()->withNoArgs()->andReturn(false);

        $this->assertFalse($this->sup->isTerminated());
    }

    public function testUnterminatedPing()
    {
        $this->supA->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);
        $this->supC->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);

        $this->assertTrue($this->sup->ping());
    }

    public function testPartiallyTerminatedPing()
    {
        $this->supA->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('ping')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);

        $this->handler->shouldReceive('handlePass')->once()->with(0, $this->sup);

        $this->assertTrue($this->sup->ping());
    }

    public function testUnsuccessfullyTerminatedPing()
    {
        $exception = new Exception('foo');

        $this->supA->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);
        $this->supB->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);
        $this->supC->shouldReceive('ping')->once()->withNoArgs()->andThrow($exception);

        $this->handler->shouldReceive('handleFail')->once()->with(0, $this->sup, $exception);

        $this->assertTrue($this->sup->ping());
    }

    public function testFullyTerminatedPing()
    {
        $this->supA->shouldReceive('ping')->once()->withNoArgs()->andReturn(false);
        $this->supB->shouldReceive('ping')->once()->withNoArgs()->andReturn(false);
        $this->supC->shouldReceive('ping')->once()->withNoArgs()->andReturn(false);

        $this->handler->shouldReceive('handlePass')->times(3)->with(0, $this->sup);

        $this->assertFalse($this->sup->ping());
    }

    public function testRestart()
    {
        $fn = function(){};

        $this->supA->shouldReceive('restart')->once()->with($fn);
        $this->supB->shouldReceive('restart')->once()->with($fn);
        $this->supC->shouldReceive('restart')->once()->with($fn);
        $this->sup->restart($fn);

        $this->assertNull($this->sup->stderr);
        $this->assertNull($this->sup->stdout);
    }

    public function testStart()
    {
        $fn = function(){};

        $this->supA->shouldReceive('start')->once()->with($fn);
        $this->supB->shouldReceive('start')->once()->with($fn);
        $this->supC->shouldReceive('start')->once()->with($fn);
        $this->sup->start($fn);

        $this->assertNull($this->sup->stderr);
        $this->assertNull($this->sup->stdout);
    }

    public function testStop()
    {
        $sig = 'foo';
        $this->supA->shouldReceive('stop')->once()->with($sig);
        $this->supB->shouldReceive('stop')->once()->with($sig);
        $this->supC->shouldReceive('stop')->once()->with($sig);
        $this->sup->stop($sig);
    }
}
