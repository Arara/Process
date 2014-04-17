<?php

namespace Arara\Process;

use RuntimeException;

class Control
{
    protected $signal;

    public function __construct()
    {
        $this->signal = new Control\Signal();
    }

    /**
     * @link   http://php.net/pcntl_exec
     * @param  string $path
     * @param  array[optional] $args
     * @param  array[optional] $envs
     */
    public function execute($path, array $args = array(), array $envs = array())
    {
        if (false === @pcntl_exec($path, $args, $envs)) {
            throw new RuntimeException('Error when executing command');
        }
    }

    // @codeCoverageIgnoreStart
    /**
     * This method exists to allow us to test without quit the program.
     *
     * @SuppressWarnings("exit")
     *
     * @link   http://php.net/exit
     * @param  int[optional] $exitCode
     */
    public function quit($exitCode = 0)
    {
        exit($exitCode);
    }
    // @codeCoverageIgnoreEnd

    /**
     * @link   http://php.net/pcntl_fork
     * @throws RuntimeException When fork fails.
     * @return int When is the child process returns "0" unless returns the child PID.
     */
    public function fork()
    {
        $processId = @pcntl_fork();
        if ($processId === -1) {
            throw new RuntimeException('Unable to fork process');
        }

        return $processId;
    }

    /**
     * Returns a signal controller.
     *
     * @return Control\Signal
     */
    public function signal()
    {
        return $this->signal;
    }

    /**
     * @link   http://php.net/pcntl_wait
     * @param  int[optional] $status
     * @param  int[optional] $options
     * @return int
     */
    public function wait(&$status = null, $options = 0)
    {
        return pcntl_wait($status, $options);
    }

    /**
     * @link   http://php.net/pcntl_waitpid
     * @return int
     */
    public function waitProcessId($processId, &$status = null, $options = 0)
    {
        return pcntl_waitpid($processId, $status, $options);
    }
}
