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
namespace Graze\Supervisor\Handler;

use Exception;
use Graze\Supervisor\Exception\UnexpectedTerminationException;
use Graze\Supervisor\SupervisorInterface;

class ExceptionHandler extends DecoratedHandler
{
    /**
     * @param integer $retries
     * @param SupervisorInterface $supervisor
     * @param Exception $exception
     */
    public function handleFail($retries, SupervisorInterface $supervisor, Exception $exception = null)
    {
        if ($exception) {
            throw $exception;
        }

        $this->handleNextFail($retries, $supervisor, $exception);
    }

    /**
     * @param integer $retries
     * @param SupervisorInterface $supervisor
     */
    public function handlePass($retries, SupervisorInterface $supervisor)
    {
        $this->handleNextPass($retries, $supervisor);
    }
}
