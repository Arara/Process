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

use Arara\Process\Exception\RuntimeException;
use Countable;
use SplObjectStorage;

/**
 * Handles pools of process.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Pool implements Process, Countable
{
    /**
     * @var SplObjectStorage
     */
    protected $process;

    /**
     * @var integer
     */
    protected $processLimit;

    /**
     * @var boolean
     */
    protected $running;

    /**
     * @var boolean
     */
    protected $stopped;

    /**
     * @param integer $processLimit Number of process in the pool.
     * @param boolean $autoStart    Starts pool automatically when TRUE (default: FALSE)
     */
    public function __construct($processLimit, $autoStart = false)
    {
        $this->process = new SplObjectStorage();
        $this->processLimit = (integer) $processLimit;
        $this->running = (boolean) $autoStart;
        $this->stopped = false;
    }

    /**
     * Return the number of active process in the pool.
     *
     * @return integer
     */
    public function count()
    {
        return $this->process->count();
    }

    /**
     * Attachs a new process to the pool.
     *
     * Try to detach finished processes from the pool when reaches its limit.
     *
     * @param Process $process
     *
     * @return null
     */
    public function attach(Process $process)
    {
        if ($this->stopped) {
            throw new RuntimeException('Could not attach child to non-running pool');
        }

        $firstProcess = $this->getFirstProcess();
        if ($this->isRunning()
            && $firstProcess instanceof Process
            && $this->count() >= $this->processLimit) {
            $firstProcess->wait();
            $this->detach($firstProcess);
        }
        $this->process->attach($process);

        if ($this->isRunning()) {
            $process->start();
        }
    }

    /**
     * Detachs a process from the pool.
     *
     * @param Process $process
     *
     * @return null
     */
    public function detach(Process $process)
    {
        $this->process->detach($process);
    }

    /**
     * Returns the fist process in the queue.
     *
     * Try to detach finished processes from the pool.
     *
     * @return Process|null
     */
    public function getFirstProcess()
    {
        $firstProcess = null;
        foreach ($this->process as $process) {
            if ($this->isRunning() && ! $process->isRunning()) {
                $this->detach($process);
                continue;
            }

            if (null !== $firstProcess) {
                continue;
            }
            $firstProcess = $process;
        }

        return $firstProcess;
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        if ($this->stopped) {
            return false;
        }

        return $this->running;
    }

    /**
     * {@inheritDoc}
     */
    public function kill()
    {
        $result = $this->isRunning();
        $this->stopped = true;
        foreach ($this->process as $process) {
            if (! $process->isRunning()) {
                continue;
            }
            $result = $process->kill() && $result;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function start()
    {
        if ($this->isRunning()) {
            throw new RuntimeException('Pool is already running');
        }

        $this->running = true;

        $processernCount = 0;
        $previousChild = null;
        foreach ($this->process as $process) {
            if ($previousChild instanceof Process && $processernCount >= $this->processLimit) {
                $previousChild->wait();
                $processernCount--;
            }

            if (! $process->isRunning()) {
                $processernCount++;
                $process->start();
            }

            $previousChild = $process;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function terminate()
    {
        $result = $this->isRunning();
        $this->stopped = true;
        foreach ($this->process as $process) {
            if (! $process->isRunning()) {
                continue;
            }
            $result = $process->terminate() && $result;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        $result = $this->isRunning();
        foreach ($this->process as $process) {
            $result = $process->wait() && $result;
        }

        return $result;
    }
}
