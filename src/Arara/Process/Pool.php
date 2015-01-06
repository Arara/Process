<?php

namespace Arara\Process;

use Arara\Process\Exception\RuntimeException;
use Countable;
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
