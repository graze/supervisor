<?php
namespace Graze\Supervisor;

use Mockery as m;
use Graze\Supervisor\Test\TestCase;

class ProcessSupervisorTest extends TestCase
{
    /** @var mixed */
    private $process;
    /** @var mixed */
    private $handler;
    /** @var ProcessSupervisor */
    private $sup;

    public function setUp()
    {
        $this->process = m::mock('Symfony\Component\Process\Process', ['stop' => null]);
        $this->handler = m::mock('Graze\Supervisor\Handler\HandlerInterface');

        $this->sup = new ProcessSupervisor($this->process, $this->handler);
    }

    public function tearDown()
    {
        $this->process->shouldReceive('stop');
    }

    public function testInterface()
    {
        $this->assertInstanceOf('Graze\Supervisor\SupervisorInterface', $this->sup);
    }

    public function testUnterminatedPing()
    {
        $this->process->shouldReceive('isTerminated')->once()->andReturn(false);

        $this->assertTrue($this->sup->ping());
    }

    public function testSuccessfullyTerminatedPing()
    {
        $err = 'foo';
        $out = 'bar';

        $this->process->shouldReceive('isTerminated')->once()->andReturn(true);
        $this->process->shouldReceive('isSuccessful')->once()->andReturn(true);
        $this->process->shouldReceive('getErrorOutput')->once()->andReturn($err);
        $this->process->shouldReceive('getOutput')->once()->andReturn($out);
        $this->handler->shouldReceive('handlePass')->once()->with(0, $this->sup);

        $this->assertFalse($this->sup->ping());
        $this->assertEquals($err, $this->sup->stderr);
        $this->assertEquals($out, $this->sup->stdout);
    }

    public function testUnsuccessfullyTerminatedPing()
    {
        $err = 'foo';
        $out = 'bar';

        $this->process->shouldReceive('isTerminated')->once()->andReturn(true);
        $this->process->shouldReceive('isSuccessful')->once()->andReturn(false);
        $this->process->shouldReceive('getErrorOutput')->times(2)->andReturn($err);
        $this->process->shouldReceive('getOutput')->times(2)->andReturn($out);
        $this->process->shouldReceive('getCommandLine')->once()->andReturn('cli');
        $this->process->shouldReceive('getExitCode')->once()->andReturn(123);
        $this->process->shouldReceive('getExitCodeText')->once()->andReturn('baz');

        $this->handler->shouldReceive('handleFail')
                      ->once()
                      ->with(0, $this->sup, m::type('Graze\Supervisor\Exception\TerminatedProcessException'));

        $this->assertFalse($this->sup->ping());
        $this->assertEquals($err, $this->sup->stderr);
        $this->assertEquals($out, $this->sup->stdout);
    }

    public function testRestart()
    {
        $fn = function () {
        };

        $process = clone $this->process;
        $this->process->shouldReceive('restart')->once()->with($fn)->andReturn($process);

        $this->assertSame($this->sup, $this->sup->restart($fn));

        $this->assertNull($this->sup->stderr);
        $this->assertNull($this->sup->stdout);

        // Check wrapped process is the new one
        $process->shouldReceive('isTerminated')->once()->andReturn(false);
        $this->sup->ping();
    }

    public function testStart()
    {
        $fn = function () {
        };

        $this->process->shouldReceive('start')->once()->with($fn);

        $this->assertSame($this->sup, $this->sup->start($fn));

        $this->assertNull($this->sup->stderr);
        $this->assertNull($this->sup->stdout);
    }

    public function testStop()
    {
        $sig = 'foo';
        $this->process->shouldReceive('stop')->once()->with(0, $sig);

        $this->assertSame($this->sup, $this->sup->stop($sig));
    }
}
