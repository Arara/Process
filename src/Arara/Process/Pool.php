<?php

namespace Arara\Process;

use InvalidArgumentException;

class Pool extends \SplObjectStorage
{
    public function attach($process, $information = null)
    {
        if (! $process instanceof Item) {
            throw new InvalidArgumentException('Object must be instance of Arara\\Process\\Item');
        }

        return parent::attach($process, $information);
    }

    public function getFirstRunning()
    {
        $firstProccess = null;
        foreach ($this as $process) {
            if (false === $process->isRunning()) {
                $this->detach($process);
                continue;
            }

            if (null !== $firstProccess) {
                continue;
            }
            $firstProccess = $process;
        }

        return $firstProccess;
    }

    public function detach($process)
    {
        if (! $process instanceof Item) {
            throw new InvalidArgumentException('Object must be instance of Arara\\Process\\Item');
        }
        $process->wait();
        $process->getIpc()->destroy();

        return parent::detach($process);
    }
}
