<?php

/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process;

use Arara\Process\Control\Info;
use Arara\Process\Control\Signal;
use Arara\Process\Exception\InvalidArgumentException;
use Arara\Process\Exception\RuntimeException;
use Arara\Process\Handler\SignalChild;
use Arara\Process\Handler\SignalInterrupt;
use Arara\Process\Handler\SignalQuit;
use Arara\Process\Handler\SignalTerminate;

/**
 * Process controller.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Control
{
    /**
     * @var Info
     */
    protected $info;

    /**
     * @var Signal
     */
    protected $signal;

    /**
     * Creates required internal instances.
     *
     * Load default signal handlers.
     */
    public function __construct()
    {
        $this->info = new Info();
        $this->signal = new Signal();
        $this->signal->setHandler('child', new SignalChild($this));
        $this->signal->setHandler('interrupt', new SignalInterrupt($this));
        $this->signal->setHandler('quit', new SignalQuit($this));
        $this->signal->setHandler('terminate', new SignalTerminate($this));
    }

    /**
     * Executes specified program in current process space.
     *
     * @param string $path
     * @param array  $args
     * @param array  $envs
     *
     * @throws RuntimeException When get an error.
     */
    public function execute($path, array $args = [], array $envs = [])
    {
        if (false === @pcntl_exec($path, $args, $envs)) {
            throw new RuntimeException('Error when executing command');
        }
    }

    // @codeCoverageIgnoreStart
    /**
     * Terminate the current program.
     *
     * @SuppressWarnings("exit")
     *
     * @param int $exitCode Optional exit code.
     */
    public function quit($exitCode = 0)
    {
        exit($exitCode);
    }
    // @codeCoverageIgnoreEnd

    /**
     * Try to flush current process memory.
     *
     * - Delays the program execution
     * - Clears file status cache
     * - Forces collection of any existing garbage cycles
     *
     *
     * @param float|int $seconds Seconds to sleep (can be 0.5)
     *
     * @throws InvalidArgumentException When $seconds is not a valid value.
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
     * Forks the current process.
     *
     * @throws RuntimeException When fork fails.
     *
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
     * Returns the process information controller.
     *
     * @return Info
     */
    public function info()
    {
        return $this->info;
    }

    /**
     * Returns the process signal controller.
     *
     * @return Signal
     */
    public function signal()
    {
        return $this->signal;
    }

    /**
     * Waits on or returns the status of a forked child.
     *
     * @param int $status
     * @param int $options
     *
     * @return int
     */
    public function wait(&$status = null, $options = 0)
    {
        return pcntl_wait($status, $options);
    }

    /**
     * Waits on or returns the status of a forked child by its id (PID).
     *
     * @param int $processId
     * @param int $status
     * @param int $options
     *
     * @return int
     */
    public function waitProcessId($processId, &$status = null, $options = 0)
    {
        return pcntl_waitpid($processId, $status, $options);
    }
}
