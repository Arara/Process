<?php

namespace Arara\Process\Control;

use Arara\Process\Exception\InvalidArgumentException;

class Status
{
    protected $status;

    /**
     * Create object and define the wait status to be analized for the object methods.
     *
     * @throws InvalidArgumentException When status is not valid.
     * @param  int $status
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
     * @return int
     */
    public function getExitStatus()
    {
        return pcntl_wexitstatus($this->status);
    }

    /**
     * Returns the signal which caused the child to stop.
     *
     * @return int
     */
    public function getStopSignal()
    {
        return pcntl_wstopsig($this->status);
    }

    /**
     * Returns the signal which caused the child to terminate.
     *
     * @return int
     */
    public function getTerminateSignal()
    {
        return pcntl_wtermsig($this->status);
    }

    /**
     * Checks if status code represents a normal exit.
     *
     * @return bool
     */
    public function isExited()
    {
        return pcntl_wifexited($this->status);
    }

    /**
     * Checks whether the status code represents a termination due to a signal.
     *
     * @return bool
     */
    public function isSignaled()
    {
        return pcntl_wifsignaled($this->status);
    }

    /**
     * Checks whether the child process is currently stopped.
     *
     * @return bool
     */
    public function isStopped()
    {
        return pcntl_wifstopped($this->status);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return (0 === $this->getExitStatus());
    }
}
