<?php

namespace Arara\Process;

use InvalidArgumentException;
use RuntimeException;

class Manager
{
    private $maxChildren;
    private $pid;
    private $pool;
    private $signal;

    public function __construct($maxChildren)
    {
        if (! filter_var($maxChildren, FILTER_VALIDATE_INT)
                || $maxChildren < 1) {
            throw new InvalidArgumentException('Children number is not valid');
        }

        $this->maxChildren = $maxChildren;
        $this->pid = posix_getpid();
        $this->pool = new Pool();
        $this->signal = new Signal();
    }

    public function getMaxChildren()
    {
        return $this->maxChildren;
    }

    public function getPid()
    {
        return $this->pid;
    }

    public function addChild(Process $process, $priority = 0)
    {
        $firstProcess = $this->pool->getFirstRunning();
        if (null !== $firstProcess
            && $this->pool->count() > $this->getMaxChildren()) {
            $this->pool->detach($firstProcess);
        }

        $this->pool->attach($process);
        if (! $process->start($this->signal)) {
            throw new RuntimeException('Could not start process');
        }

        if ($priority != 0) {
            $process->setPriority($priority);
        }

        return $this;
    }

    public function wait()
    {
        foreach ($this->pool as $process) {
            if (false === $process->isRunning()) {
                continue;
            }
            $process->wait();
            $process->getIpc()->destroy();
        }
    }

    public function stop()
    {
        foreach ($this->pool as $process) {
            if (false === $process->isRunning()) {
                continue;
            }
            $process->stop();
            $process->getIpc()->destroy();
        }
    }

    public function kill()
    {
        foreach ($this->pool as $process) {
            if (false === $process->isRunning()) {
                continue;
            }
            $process->kill();
            $process->getIpc()->destroy();
        }
    }

    public function __destruct()
    {
        $this->wait();
    }
}
