<?php
/*
 * This file is part of Graze Supervisor
 *
 * Copyright (c) 2014 Nature Delivered Ltd. <http://graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see  http://github.com/graze/process/blob/master/LICENSE
 * @link http://github.com/graze/process
 */
namespace Graze\Supervisor\Exception;

use Exception;
use RuntimeException;
use Symfony\Component\Process\Process;

class TerminatedProcessException extends RuntimeException implements ProcessExceptionInterface
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @param Process $process
     * @param integer $code
     * @param Exception $previous
     */
    public function __construct(Process $process, $code = 0, Exception $previous = null)
    {
        $this->process = $process;
        $message  = 'The process was unexpectedly terminated' . PHP_EOL;
        $message .= implode(PHP_EOL, $this->formatProcess($process));
        $message .= PHP_EOL;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @param Process $process
     * @return string[]
     */
    protected function formatProcess(Process $process)
    {
        return [
            '[process] ' . $process->getCommandLine(),
            '[code]    ' . $process->getExitCode(),
            '[text]    ' . $process->getExitCodeText(),
            '[stderr]  ' . trim($process->getErrorOutput()),
            '[stdout]  ' . trim($process->getOutput())
        ];
    }
}
