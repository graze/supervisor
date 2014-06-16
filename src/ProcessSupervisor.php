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
    public function isRunning()
    {
        return $this->process->isRunning();
    }

    /**
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->process->isSuccessful();
    }

    /**
     * @return boolean
     */
    public function isTerminated()
    {
        return $this->process->isTerminated();
    }

    /**
     * @return boolean
     */
    public function ping()
    {
        if ($this->isTerminated()) {
            $this->stderr = $this->process->getErrorOutput();
            $this->stdout = $this->process->getOutput();

            if ($this->isSuccessful()) {
                $this->handler->handlePass($this->retries, $this);
            } else {
                $this->handler->handleFail($this->retries, $this, new TerminatedProcessException($this->process));
            }

            return false;
        }

        return true;
    }

    /**
     * @param callable $fn
     */
    public function restart(callable $fn = null)
    {
        $this->retries += 1;
        $this->process = $this->process->restart($fn);
    }

    /**
     * @param callable $fn
     */
    public function start(callable $fn = null)
    {
        $this->retries = 0;
        $this->process->start($fn);
    }

    /**
     * @param integer $signal
     */
    public function stop($signal = null)
    {
        $this->process->stop(0, $signal);
    }

    /**
     * @param float $delay
     */
    public function supervise($delay = 0.001)
    {
        $microdelay = $delay * 100000;

        while ($this->ping(true)) {
            usleep($microdelay);
        }
    }

    /**
     * @return HandlerInterface
     */
    protected function getDefaultHandler()
    {
        return new ExceptionHandler(new UnexpectedTerminationHandler());
    }
}
