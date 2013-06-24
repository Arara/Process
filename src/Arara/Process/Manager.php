<?php

namespace Arara\Process;

class Manager
{

    private $queue;
    private $signalHandler;
    private $processId;
    private $getMaxChildren;

    public function __construct($getMaxChildren)
    {
        if (!filter_var($getMaxChildren, FILTER_VALIDATE_INT)
                || $getMaxChildren < 1) {
            throw new \InvalidArgumentException('Children number is not valid');
        }

        $this->queue = new Queue();
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
        if ($this->queue->count() == $this->getMaxChildren()) {
            $this->queue->extract()->wait();
        }

        $process->start($this->signalHandler);

        if (!pcntl_setpriority($priority, $this->getPid(), PRIO_PROCESS)) {
            $message = 'Unable to set the priority';
            throw new \RuntimeException($message);
        }

        $this->queue->insert($process, $priority);

        return $this;
    }

    public function __destruct()
    {
        foreach ($this->queue as $process) {
            $process->wait();
        }
    }

}
