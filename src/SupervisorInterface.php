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

interface SupervisorInterface
{
    /**
     * @return boolean
     */
    public function ping();

    /**
     * @param callable $fn
     * @return SupervisorInterface
     */
    public function restart(callable $fn = null);

    /**
     * @param callable $fn
     * @return SupervisorInterface
     */
    public function start(callable $fn = null);

    /**
     * @param integer $signal
     * @return SupervisorInterface
     */
    public function stop($signal = null);

    /**
     * @param float $delay
     * @return SupervisorInterface
     */
    public function supervise($delay = 0.001);
}
