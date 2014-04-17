<?php

namespace Arara\Process;

use Countable;
use IteratorAggregate;
use RuntimeException;
use SplObjectStorage;

class Pool implements Process, Countable
{
    protected $process;
    protected $processLimit;
    protected $running;
    protected $stopped;

    public function __construct($processLimit, $autoStart = false)
    {
        $this->process = new SplObjectStorage();
        $this->processLimit = $processLimit;
        $this->running = (bool) $autoStart;
        $this->stopped = false;
    }

    public function count()
    {
        return $this->process->count();
    }

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

    public function detach(Process $process)
    {
        $this->process->detach($process);
    }

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
        if (! $this->isRunning()) {
            throw new RuntimeException('Cannot kill a non-running pool');
        }

        $this->stopped = true;
        foreach ($this->process as $process) {
            if (! $process->isRunning()) {
                continue;
            }
            $process->kill();
        }
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
        if (! $this->isRunning()) {
            throw new RuntimeException('Cannot terminate a non-running pool');
        }

        $this->stopped = true;
        foreach ($this->process as $process) {
            if (! $process->isRunning()) {
                continue;
            }
            $process->terminate();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        if (! $this->isRunning()) {
            throw new RuntimeException('Cannot wait a non-running pool');
        }

        foreach ($this->process as $process) {
            if (! $process->isRunning()) {
                continue;
            }
            $process->wait();
        }
    }
}
