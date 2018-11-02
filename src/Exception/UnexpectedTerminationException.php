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
namespace Graze\Supervisor\Exception;

use Graze\Supervisor\SupervisorInterface;
use Exception;
use RuntimeException;

class UnexpectedTerminationException extends RuntimeException implements SupervisorExceptionInterface
{
    /**
     * @var SupervisorInterface
     */
    protected $supervisor;

    /**
     * @param SupervisorInterface $supervisor
     * @param int $code
     * @param Exception $previous
     */
    public function __construct(SupervisorInterface $supervisor, $code = 0, Exception $previous = null)
    {
        $this->supervisor = $supervisor;

        parent::__construct('The supervisor was unexpectedly terminated.', $code, $previous);
    }

    /**
     * @return SupervisorInterface
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }
}
