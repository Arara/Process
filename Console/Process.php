<?php

/**
 * @namespace
 */
namespace PHProcess\Console;

/**
 * Class that handle creating multiple process.
 *
 * This source file is subject to the GNU/GPLv3 license.
 *
 * @category   PHProcess
 * @package    PHProcess\Console
 * @subpackage PHProcess\Console\Process
 * @author     Cyril Nicodème
 * @author     Henrique Moody <henriquemoody@gmail.com>
 */
class Process
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
     * Constructor.
     *
     * Checks whether the system meets the requirements needed to run the class.
     */
    public function __construct()
    {
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $message = 'Cannot run on windows';
            throw new \UnexpectedValueException($message);

        } else if (!in_array(substr(PHP_SAPI, 0, 3), array('cli', 'cgi'))) {
            $message = 'Can only run on CLI or CGI enviroment';
            throw new \UnexpectedValueException($message);

        } else if (!function_exists('pcntl_fork')) {
            $message = 'pcntl_* functions are required';
            throw new \UnexpectedValueException();

        } else if (!function_exists('posix_setgid')) {
            $message = 'posix_* functions are required';
            throw new \UnexpectedValueException($message);

        }
    }

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
     * @return  PHProcess\Console\Process\Fork Forked process
     */
    public function fork($callback, $uid = null, $gid = null)
    {
        $fork = new Process\Fork();
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
     * @return  PHProcess\Console\Process Fluent interface, returns self
     */
    public function setMaxChildren($value)
    {
        if (!is_int($value) || $value < 1) {
            $message = 'Children must be an int';
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
     * @return  void
     */
    public static function setDefaultMaxChildren($int)
    {
        if (!is_int($value) || $value < 1) {
            $message = 'Children must be an int';
            throw new \InvalidArgumentException($message);
        }
        self::$_defaultMaxChildren = $int;
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


