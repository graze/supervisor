<?php

namespace Graze\Supervisor\Exception;

use Graze\Supervisor\Test\TestCase;
use Mockery as m;

class TerminatedProcessExceptionTest extends TestCase
{
    /** @var mixed */
    private $process;

    public function setUp()
    {
        $this->process = m::mock('Symfony\Component\Process\Process', ['stop' => null]);
    }

    public function testInterface()
    {

        $this->process->shouldReceive('getCommandLine')->once()->andReturn('foo');
        $this->process->shouldReceive('getExitCode')->once()->andReturn(1);
        $this->process->shouldReceive('getExitCodeText')->once()->andReturn('bar');
        $this->process->shouldReceive('getOutput')->once()->andReturn('baz');
        $this->process->shouldReceive('getErrorOutput')->once()->andReturn('bam');

        $exception = new TerminatedProcessException($this->process);

        $this->assertInstanceOf('Graze\Supervisor\Exception\ExceptionInterface', $exception);
        $this->assertInstanceOf('Graze\Supervisor\Exception\ProcessExceptionInterface', $exception);
    }

    public function testGetProcess()
    {
        $this->process->shouldReceive('getCommandLine')->once()->andReturn('foo');
        $this->process->shouldReceive('getExitCode')->once()->andReturn(1);
        $this->process->shouldReceive('getExitCodeText')->once()->andReturn('bar');
        $this->process->shouldReceive('getOutput')->once()->andReturn('baz');
        $this->process->shouldReceive('getErrorOutput')->once()->andReturn('bam');

        $exception = new TerminatedProcessException($this->process);

        $this->assertSame($this->process, $exception->getProcess());
    }
}
