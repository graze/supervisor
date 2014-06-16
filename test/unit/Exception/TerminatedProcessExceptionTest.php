<?php
namespace Graze\Supervisor\Exception;

use Exception;
use Mockery as m;

class TerminatedProcessExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->process = m::mock('Symfony\Component\Process\Process', ['stop' => null]);
    }

    public function testInterface()
    {
        $exception = m::mock('Graze\Supervisor\Exception\TerminatedProcessException');

        $this->assertInstanceOf('Graze\Supervisor\Exception\ExceptionInterface', $exception);
        $this->assertInstanceOf('Graze\Supervisor\Exception\ProcessExceptionInterface', $exception);
    }

    public function testConstruct()
    {
        $this->process->shouldReceive('getCommandLine')->once()->withNoArgs()->andReturn('foo');
        $this->process->shouldReceive('getExitCode')->once()->withNoArgs()->andReturn(1);
        $this->process->shouldReceive('getExitCodeText')->once()->withNoArgs()->andReturn('bar');
        $this->process->shouldReceive('getOutput')->once()->withNoArgs()->andReturn('baz');
        $this->process->shouldReceive('getErrorOutput')->once()->withNoArgs()->andReturn('bam');

        $exception = new TerminatedProcessException($this->process);

        $this->assertSame($this->process, $exception->getProcess());
    }
}
