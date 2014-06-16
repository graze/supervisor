<?php
namespace Graze\Supervisor\Exception;

use Exception;
use Mockery as m;

class UnexpectedTerminationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sup = m::mock('Graze\Supervisor\SupervisorInterface');
    }

    public function testInterface()
    {
        $exception = m::mock('Graze\Supervisor\Exception\UnexpectedTerminationException');

        $this->assertInstanceOf('Graze\Supervisor\Exception\ExceptionInterface', $exception);
        $this->assertInstanceOf('Graze\Supervisor\Exception\SupervisorExceptionInterface', $exception);
    }

    public function testConstruct()
    {
        $exception = new UnexpectedTerminationException($this->sup);

        $this->assertSame($this->sup, $exception->getSupervisor());
    }
}
