<?php

/**
 * @namespace
 */
namespace Jam\Process;

/**
 * Class that handle creating multiple process.
 *
 * This source file is subject to the GNU/GPLv3 license.
 *
 * @category   Jam
 * @package    Jam\Process
 * @author     Cyril Nicodème
 * @author     Henrique Moody <henriquemoody@gmail.com>
 */
class Manager
{

    /**
     * Contain the PID of the current process.
     *
     * @var int
     */
    private $_pid;

    /**
     * Contains a list of all the children PID's.
     * (in case the current process is the father)
     *
     * @var array
     */
    private $_forks = array();

    /**
     * Contain the number of max allowed children.
     *
     * @var int
     */
    private $_maxChildren;

    /**
     * Contain the default number of max allowed children.
     *
     * @var int
     */
    private static $_defaultMaxChildren = 5;

    /**
     * Destructor.
     *
     * Suspends the execution of the childrens.
     */
    public function __destruct()
    {
        foreach ($this->_forks as $fork) {
            pcntl_waitpid($fork->getPid(), $status);
        }
    }

    /**
     * Forks a process.
     *
     * @param   array|string|Cousure $callback
     * @param   int[optional] $uid
     * @param   int[optional] $gid
     * @return  Jam\Process\Fork Forked process object
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

        $this->_forks[] = $fork;

        if (count($this->_forks) >= $this->getMaxChildren()) {
            $first = array_shift($this->_forks);
            pcntl_waitpid($first->getPid(), $status);
        }
        return $fork;
    }

    /**
     * Define the the number of max allowed children.
     *
     * @param   int $value
     * @return  Jam\Process\Manager Fluent interface, returns self
     */
    public function setMaxChildren($value)
    {
        if (!is_int($value) || $value < 1) {
            $message = 'Children must be an int and greater than 1';
            throw new \InvalidArgumentException($message);
        }

        $this->_maxChildren = $value;
        return $this;
    }

    /**
     * Returns the number of max allowed children.
     *
     * @return int
     */
    public function getMaxChildren()
    {
        if (null === $this->_maxChildren) {
            $this->_maxChildren = self::getDefaultMaxChildren();
        }
        return $this->_maxChildren;
    }

    /**
     * Return the default number of childrens.
     *
     * @return  int
     */
    public static function getDefaultMaxChildren()
    {
        return self::$_defaultMaxChildren;
    }

    /**
     * Defines the default number of childrens.
     *
     * @param   int $value
     * @return  void
     */
    public static function setDefaultMaxChildren($value)
    {
        if (!is_int($value) || $value < 1) {
            $message = 'Children must be an integer and greater than 1';
            throw new \InvalidArgumentException($message);
        }
        self::$_defaultMaxChildren = $value;
    }

    /**
     * Retursn the PID of the current process.
     *
     * @return  int
     */
    public function getPid()
    {
        if (null === $this->_pid) {
            $this->_pid = posix_getpid();
        }
        return $this->_pid;
    }


}


