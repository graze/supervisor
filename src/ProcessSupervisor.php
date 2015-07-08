<?php
/*
 * This file is part of Graze Supervisor
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <http://graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/supervisor/blob/master/LICENSE
 * @link http://github.com/graze/supervisor
 */
namespace Graze\Supervisor;

use Graze\Supervisor\Exception\TerminatedProcessException;
use Graze\Supervisor\Handler\ExceptionHandler;
use Graze\Supervisor\Handler\HandlerInterface;
use Graze\Supervisor\Handler\UnexpectedTerminationHandler;
use Symfony\Component\Process\Process;

class ProcessSupervisor implements SupervisorInterface
{
    public $stderr;
    public $stdout;

    protected $handler;
    protected $process;
    protected $retries = 0;

    /**
     * @param Process $process
     * @param HandlerInterface $handler
     */
    public function __construct(Process $process, HandlerInterface $handler = null)
    {
        $this->handler = $handler ?: $this->getDefaultHandler();
        $this->process = $process;
    }

    /**
     * @return boolean
     */
    public function ping()
    {
        if ($this->process->isTerminated()) {
            $this->stderr = $this->process->getErrorOutput();
            $this->stdout = $this->process->getOutput();

            if ($this->process->isSuccessful()) {
                return (boolean) $this->handler->handlePass($this->retries, $this);
            }

            return (boolean) $this->handler->handleFail(
                $this->retries,
                $this,
                new TerminatedProcessException($this->process)
            );
        }

        return true;
    }

    /**
     * @param callable $fn
     * @return SupervisorInterface
     */
    public function restart(callable $fn = null)
    {
        $this->retries += 1;
        $this->process = $this->process->restart($fn);

        return $this;
    }

    /**
     * @param callable $fn
     * @return SupervisorInterface
     */
    public function start(callable $fn = null)
    {
        $this->retries = 0;
        $this->process->start($fn);

        return $this;
    }

    /**
     * @param integer $signal
     * @return SupervisorInterface
     */
    public function stop($signal = null)
    {
        $this->process->stop(0, $signal);

        return $this;
    }

    /**
     * @param float $delay
     * @return SupervisorInterface
     */
    public function supervise($delay = 0.001)
    {
        $microdelay = $delay * 100000;

        while ($this->ping()) {
            usleep($microdelay);
        }

        return $this;
    }

    /**
     * @return HandlerInterface
     */
    protected function getDefaultHandler()
    {
        return new ExceptionHandler(new UnexpectedTerminationHandler());
    }
}
