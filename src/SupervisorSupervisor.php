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

use Exception;
use Graze\Supervisor\Handler\ExceptionHandler;
use Graze\Supervisor\Handler\HandlerInterface;
use Graze\Supervisor\Handler\UnexpectedTerminationHandler;

class SupervisorSupervisor implements SupervisorInterface
{
    public $stderr;
    public $stdout;

    protected $handler;
    protected $retries = 0;
    protected $supervisors;

    /**
     * @param SupervisorInterface[] $supervisors
     * @param HandlerInterface $handler
     */
    public function __construct(array $supervisors, HandlerInterface $handler = null)
    {
        $this->handler = $handler ?: $this->getDefaultHandler();
        $this->supervisors = array_filter($supervisors, function (SupervisorInterface $supervisor) {
            return true;
        });
    }

    /**
     * @return boolean
     */
    public function ping()
    {
        $out = false;

        foreach ($this->supervisors as $supervisor) {
            try {
                if (!$supervisor->ping()) {
                    $this->updateOutput();
                    $result = $this->handler->handlePass($this->retries, $this);
                } else {
                    $result = true;
                }
            } catch (Exception $exception) {
                $this->updateOutput();
                $result = $this->handler->handleFail($this->retries, $this, $exception);
            }

            $out = $result ? true : $out;
        }

        return $out;
    }

    /**
     * @param callable $fn
     * @return SupervisorInterface
     */
    public function restart(callable $fn = null)
    {
        $this->reset($this->retries + 1);

        foreach ($this->supervisors as $supervisor) {
            $supervisor->restart($fn);
        }

        return $this;
    }

    /**
     * @param callable $fn
     * @return SupervisorInterface
     */
    public function start(callable $fn = null)
    {
        $this->reset(0);

        foreach ($this->supervisors as $supervisor) {
            $supervisor->start($fn);
        }

        return $this;
    }

    /**
     * @param integer $signal
     * @return SupervisorInterface
     */
    public function stop($signal = null)
    {
        foreach ($this->supervisors as $supervisor) {
            $supervisor->stop($signal);
        }

        return $this;
    }

    /**
     * @param float $delay
     * @return SupervisorInterface
     */
    public function supervise($delay = 0.001)
    {
        $microdelay = $delay * 1000000;

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

    /**
     * @param integer $retries
     */
    protected function reset($retries = 0)
    {
        $this->retries = $retries;
        $this->stderr  = null;
        $this->sttout  = null;
    }

    protected function updateOutput()
    {
        foreach ($this->supervisors as $supervisor) {
            $this->stderr .= $supervisor->stderr . PHP_EOL;
            $this->stdout .= $supervisor->stdout . PHP_EOL;
        }
    }
}
