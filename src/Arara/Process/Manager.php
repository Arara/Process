<?php

namespace Arara\Process;

use RuntimeException;

class Manager
{
    private $pool;
    private $signalHandler;
    private $processId;
    private $getMaxChildren;

    public function __construct($getMaxChildren)
    {
        if (! filter_var($getMaxChildren, FILTER_VALIDATE_INT)
                || $getMaxChildren < 1) {
            throw new \InvalidArgumentException('Children number is not valid');
        }

        $this->pool = new Pool();
        $this->signalHandler = new SignalHandler();
        $this->processId = posix_getpid();
        $this->getMaxChildren = $getMaxChildren;
    }

    public function getMaxChildren()
    {
        return $this->getMaxChildren;
    }

    public function getPid()
    {
        return $this->processId;
    }

    public function addChild(Item $process, $priority = 0)
    {
        $firstProcess = $this->pool->getFirstRunning();
        if (null !== $firstProcess
            && $this->pool->count() > $this->getMaxChildren()) {
            $this->pool->detach($firstProcess);
        }

        $this->pool->attach($process);
        if (! $process->start($this->signalHandler)) {
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
