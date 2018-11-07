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
use Graze\Supervisor\SupervisorInterface;

abstract class AbstractDecoratedHandler implements HandlerInterface
{
    /**
     * @var HandlerInterface
     */
    protected $next;

    /**
     * @param HandlerInterface $next
     */
    public function __construct(HandlerInterface $next = null)
    {
        $this->next = $next;
    }

    /**
     * @param int $retries
     * @param SupervisorInterface $supervisor
     * @param Exception $exception
     * @return bool
     */
    protected function handleNextFail($retries, SupervisorInterface $supervisor, Exception $exception = null)
    {
        if ($this->next) {
            return $this->next->handleFail($retries, $supervisor, $exception);
        }

        return false;
    }

    /**
     * @param int $retries
     * @param SupervisorInterface $supervisor
     * @return bool
     */
    protected function handleNextPass($retries, SupervisorInterface $supervisor)
    {
        if ($this->next) {
            return $this->next->handlePass($retries, $supervisor);
        }

        return false;
    }
}
