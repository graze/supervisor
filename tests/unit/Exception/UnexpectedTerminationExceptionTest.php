<?php
namespace Graze\Supervisor\Exception;

use Exception;
use Graze\Supervisor\Exception\UnexpectedTerminationException;
use Mockery as m;

class UnexpectedTerminationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->sup = m::mock('Graze\Supervisor\SupervisorInterface');
    }

    public function testInterface()
    {
        $exception = new UnexpectedTerminationException($this->sup);

        $this->assertInstanceOf('Graze\Supervisor\Exception\ExceptionInterface', $exception);
        $this->assertInstanceOf('Graze\Supervisor\Exception\SupervisorExceptionInterface', $exception);
    }

    public function testGetSupervisor()
    {
        $exception = new UnexpectedTerminationException($this->sup);

        $this->assertSame($this->sup, $exception->getSupervisor());
    }
}
