<?php

namespace Jam\Process;

/**
 * @author Cyril Nicodème
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Manager
{

    /**
     * @var int
     */
    private $pid;

    /**
     * @var array
     */
    private $forks = array();

    /**
     * @var int
     */
    private $maxChildren;

    /**
     * @param  int $maxChildren
     **/
    public function __construct($maxChildren = 5)
    {
        if (!is_int($maxChildren) || $maxChildren < 1) {
            $message = 'Children must be an int and greater than 1';
            throw new \InvalidArgumentException($message);
        }

        $this->pid = posix_getpid();
        $this->maxChildren = $maxChildren;
    }

    /**
     * @return int
     */
    public function getMaxChildren()
    {
        return $this->maxChildren;
    }

    /**
     * @return  int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param  array|string|Cousure $callback
     * @param  int[optional] $uid
     * @param  int[optional] $gid
     * @return Jam\Process\Fork Forked process object
     */
    public function fork($callback, $uid = null, $gid = null)
    {
        $fork = new Fork();
        if (null !== $uid) {
            $fork->setUserId($uid);
        }
        if (null !== $gid) {
            $fork->setGroupId($gid);
        }
        $fork->setCallback($callback)
             ->start();

        $this->forks[] = $fork;

        if (count($this->forks) >= $this->getMaxChildren()) {
            $first = array_shift($this->forks);
            pcntl_waitpid($first->getPid(), $status);
        }

        return $fork;
    }

    public function __destruct()
    {
        foreach ($this->forks as $fork) {
            pcntl_waitpid($fork->getPid(), $status);
        }
    }

}




