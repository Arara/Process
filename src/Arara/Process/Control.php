<?php

namespace Arara\Process;

use InvalidArgumentException;
use RuntimeException;

class Control
{
    protected $info;
    protected $signal;

    public function __construct()
    {
        $this->info = new Control\Info();
        $this->signal = new Control\Signal();
        $this->signal->setHandler('child', new Handler\SignalChild($this));
        $this->signal->setHandler('interrupt', new Handler\SignalInterrupt($this));
        $this->signal->setHandler('quit', new Handler\SignalQuit($this));
        $this->signal->setHandler('terminate', new Handler\SignalTerminate($this));
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
     * Try to flush current process memory.
     *
     * - Delays the program execution;
     * - Clears file status cache;
     * - Forces collection of any existing garbage cycles.
     *
     * @throws InvalidArgumentException When $seconds is not a valid value.
     * @param  float|int[optional] $seconds Seconds to sleep (can be 0.5)
     * @return void
     */
    public function flush($seconds = 0)
    {
        if (! (is_float($seconds) || is_int($seconds)) || $seconds < 0) {
            throw new InvalidArgumentException('Seconds must be a number greater than or equal to 0');
        }

        if (is_int($seconds)) {
            sleep($seconds);
        } else {
            usleep($seconds * 1000000);
        }

        clearstatcache();
        gc_collect_cycles();
    }

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
     * Returns a info controller.
     *
     * @return Control\Info
     */
    public function info()
    {
        return $this->info;
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
