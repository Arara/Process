<?php

namespace Arara\Process;

class Queue extends \SplPriorityQueue
{

    public function insert($process, $priority)
    {
        if (!$process instanceof Item) {
            $message = 'You must insert only process items';
            throw new \InvalidArgumentException($message);
        }

        return parent::insert($process, $priority);
    }

}
