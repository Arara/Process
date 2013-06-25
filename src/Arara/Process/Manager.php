<?php

namespace Arara\Process;

use SplObjectStorage;

class Manager
{

    private $pool;
    private $signalHandler;
    private $processId;
    private $getMaxChildren;

    public function __construct($getMaxChildren)
    {
        if (!filter_var($getMaxChildren, FILTER_VALIDATE_INT)
                || $getMaxChildren < 1) {
            throw new \InvalidArgumentException('Children number is not valid');
        }

        $this->pool = new SplObjectStorage();
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
        if ($this->pool->count() == $this->getMaxChildren()) {
            $found = false;
            foreach ($this->pool as $poolProcess) {
                if (false === $poolProcess->isRunning()) {
                    $this->pool->detach($poolProcess);
                } elseif (false === $found) {
                    $poolProcess->wait();
                    $found = true;
                }
            }
        }

        $this->pool->attach($process);

        $process->start($this->signalHandler);

        if (true === $process->hasPid()) {
            $process->setPriority($priority);
        }

        return $this;
    }

    public function wait()
    {
        foreach ($this->pool as $process) {
            if (false === $process->hasPid()) {
                continue;
            }
            $process->wait();
        }
    }

    public function stop()
    {
        foreach ($this->pool as $process) {
            if (false === $process->hasPid()) {
                continue;
            }
            $process->stop();
        }
    }

    public function __destruct()
    {
        $this->wait();
    }

}
