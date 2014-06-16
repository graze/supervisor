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

abstract class DecoratedHandler implements HandlerInterface
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
     * @param integer $retries
     * @param SupervisorInterface $supervisor
     * @param Exception $exception
     */
    protected function handleNextFail($retries, SupervisorInterface $supervisor, Exception $exception = null)
    {
        if ($this->next) {
            $this->next->handleFail($retries, $supervisor, $exception);
        }
    }

    /**
     * @param integer $retries
     * @param SupervisorInterface $supervisor
     */
    protected function handleNextPass($retries, SupervisorInterface $supervisor)
    {
        if ($this->next) {
            $this->next->handlePass($retries, $supervisor);
        }
    }
}
