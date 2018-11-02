<?php
namespace Graze\Supervisor;

use Exception;
use Graze\Supervisor\Test\TestCase;
use Mockery as m;

class SupervisorSupervisorTest extends TestCase
{
    /** @var mixed */
    private $handler;
    /** @var mixed */
    private $supA;
    /** @var mixed */
    private $supB;
    /** @var mixed */
    private $supC;
    /** @var mixed */
    private $sups;
    /** @var SupervisorSupervisor */
    private $sup;

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

    public function testUnterminatedPing()
    {
        $this->supA->shouldReceive('ping')->once()->andReturn(true);
        $this->supB->shouldReceive('ping')->once()->andReturn(true);
        $this->supC->shouldReceive('ping')->once()->andReturn(true);

        $this->handler->shouldReceive('handleFail');

        $this->assertTrue($this->sup->ping());
    }

    public function testPartiallyTerminatedPing()
    {
        $this->supA->shouldReceive('ping')->once()->andReturn(true);
        $this->supB->shouldReceive('ping')->once()->andReturn(false);
        $this->supC->shouldReceive('ping')->once()->andReturn(true);

        $this->handler->shouldReceive('handlePass')->once()->with(0, $this->sup);

        $this->assertTrue($this->sup->ping());
    }

    public function testUnsuccessfullyTerminatedPing()
    {
        $exception = new Exception('foo');

        $this->supA->shouldReceive('ping')->once()->andReturn(true);
        $this->supB->shouldReceive('ping')->once()->andReturn(true);
        $this->supC->shouldReceive('ping')->once()->andThrow($exception);

        $this->handler->shouldReceive('handleFail')->once()->with(0, $this->sup, $exception);

        $this->assertTrue($this->sup->ping());
    }

    public function testFullyTerminatedPing()
    {
        $this->supA->shouldReceive('ping')->once()->andReturn(false);
        $this->supB->shouldReceive('ping')->once()->andReturn(false);
        $this->supC->shouldReceive('ping')->once()->andReturn(false);

        $this->handler->shouldReceive('handlePass')->times(3)->with(0, $this->sup);

        $this->assertFalse($this->sup->ping());
    }

    public function testRestart()
    {
        $fn = function () {
        };

        $this->supA->shouldReceive('restart')->once()->with($fn);
        $this->supB->shouldReceive('restart')->once()->with($fn);
        $this->supC->shouldReceive('restart')->once()->with($fn);

        $this->assertSame($this->sup, $this->sup->restart($fn));

        $this->assertNull($this->sup->stderr);
        $this->assertNull($this->sup->stdout);
    }

    public function testStart()
    {
        $fn = function () {
        };

        $this->supA->shouldReceive('start')->once()->with($fn);
        $this->supB->shouldReceive('start')->once()->with($fn);
        $this->supC->shouldReceive('start')->once()->with($fn);

        $this->assertSame($this->sup, $this->sup->start($fn));

        $this->assertNull($this->sup->stderr);
        $this->assertNull($this->sup->stdout);
    }

    public function testStop()
    {
        $sig = 'foo';
        $this->supA->shouldReceive('stop')->once()->with($sig);
        $this->supB->shouldReceive('stop')->once()->with($sig);
        $this->supC->shouldReceive('stop')->once()->with($sig);

        $this->assertSame($this->sup, $this->sup->stop($sig));
    }
}
