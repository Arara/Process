<?php
/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Control;

use Arara\Process\Exception\InvalidArgumentException;

/**
 * Process status information.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Status
{
    /**
     * @var integer
     */
    protected $status;

    /**
     * Create object and define the wait status to be analized for the object methods.
     *
     * @throws InvalidArgumentException When status is not valid.
     *
     * @param integer $status
     */
    public function __construct($status)
    {
        if (! is_int($status)) {
            throw new InvalidArgumentException('Invalid wait status given');
        }
        $this->status = $status;
    }

    /**
     * Returns the exit code of a terminated child.
     *
     * @return integer
     */
    public function getExitStatus()
    {
        return pcntl_wexitstatus($this->status);
    }

    /**
     * Returns the signal which caused the child to stop.
     *
     * @return integer
     */
    public function getStopSignal()
    {
        return pcntl_wstopsig($this->status);
    }

    /**
     * Returns the signal which caused the child to terminate.
     *
     * @return integer
     */
    public function getTerminateSignal()
    {
        return pcntl_wtermsig($this->status);
    }

    /**
     * Checks if status code represents a normal exit.
     *
     * @return boolean
     */
    public function isExited()
    {
        return pcntl_wifexited($this->status);
    }

    /**
     * Checks whether the status code represents a termination due to a signal.
     *
     * @return boolean
     */
    public function isSignaled()
    {
        return pcntl_wifsignaled($this->status);
    }

    /**
     * Checks whether the child process is currently stopped.
     *
     * @return boolean
     */
    public function isStopped()
    {
        return pcntl_wifstopped($this->status);
    }

    /**
     * Returns TRUE when successful or FALSE if not.
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return (0 === $this->getExitStatus());
    }
}
