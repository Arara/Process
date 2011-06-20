<?php

/**
 * @namespace
 */
namespace Console;

/**
 * Class that handle creating multiple process.
 *
 *
 * This source file is subject to the GNU/GPLv3 license.
 *
 * @package     Console
 * @subpackage  Process
 * @author      Cyril Nicodème
 */
class Process
{

    /**
     * Default children count
     */
    const DEFAULT_MAX_CHILDREN = 5;

    /**
     * Contain the PID of the current process.
     *
     * @var int
     */
    private $_pid;

    /**
     * Contain the priority for the current process.
     *
     * @var int
     */
    private $_priority = 1;

    /**
     * Contains a list of all the children PID's.
     * (in case the current process is the father)
     *
     * @var array
     */
    private $_children = array();

    /**
     * Contain the number of max allowed children.
     *
     * @var int
     */
    private $_maxChildren = self::DEFAULT_MAX_CHILDREN;

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

        $this->_pid = getmypid();

        // Setting up the signal handlers
        $this->addSignal(SIGTERM, array($this, 'signalHandler'));
        $this->addSignal(SIGQUIT, array($this, 'signalHandler'));
        $this->addSignal(SIGINT, array($this, 'signalHandler'));
    }

    /**
     * Destructor.
     *
     * Suspends the execution of the childrens.
     */
    public function __destruct()
    {
        foreach ($this->_children as $childPid) {
            pcntl_waitpid($childPid, $status);
        }
    }

    /**
     * Fork a process.
     *
     * @param   array|string|Cousure $callback
     * @param   int[optional] $uid
     * @param   int[optional] $gid
     * @return  void
     */
    public function fork($callback, $uid = null, $gid = null)
    {
        if (!is_callable($callback)) {
            $message = 'Callback given must be callable';
            throw new \InvalidArgumentException($message);
        }

        $pid = pcntl_fork();

        if ($pid === -1) {
            $message = 'Unable to fork.';
            throw new \RuntimeException($message);
        } elseif ($pid > 0) {

            // We are in the parent process
            $this->_children[] = $pid;

            if (count($this->_children) >= $this->_maxChildren) {
                pcntl_waitpid(array_shift($this->_children), $status);
            }
        } elseif ($pid === 0) {

            if ($gid !== null) {
                posix_setgid($gid);
            }

            if ($uid !== null) {
                posix_setuid($uid);
            }

            // We are in the child process
            call_user_func($callback);
            exit(0);
        }
    }

    /**
     * Add a new signal that will be called to the given function with
     * an optional callback.
     *
     * @param   int $signal
     * @param   string|array|Clousure $callback
     * @return  Process Fluent interface, returns self.
     */
    public function addSignal($signal, $callback)
    {
        if (!is_int($signal)) {
            $message = 'Signal must be an integer.';
            throw new \InvalidArgumentException($message);
        }

        if (!is_callable($callback)) {
            $message = 'Callback must be callable.';
            throw new \InvalidArgumentException($message);
        }

        if (!pcntl_signal($signal, $callback)) {
            $message = 'Unable to set up the signal.';
            throw new \RuntimeException($message);
        }
        return $this;
    }

    /**
     * The default signal handler, to avoid Zombies
     *
     * @param   int $signal
     * @return  void
     */
    public function signalHandler($signal = SIGTERM)
    {
        switch ($signal) {
            case SIGTERM: // Finish
                exit(0);
                break;
            case SIGQUIT: // Quit
            case SIGINT:  // Stop from the keyboard
            case SIGKILL: // Kill
                exit(1);
                break;
        }
    }

    /**
     * Define the the number of max allowed children.
     *
     * @param   int $maxChildren
     * @return  Process Fluent interface, returns self
     */
    public function setMaxChildren($maxChildren)
    {
        if (!is_int($maxChildren) || $maxChildren < 1) {
            $message = 'Children must be an int';
            throw new \InvalidArgumentException($message);
        }

        $this->_maxChildren = $maxChildren;
        return $this;
    }

    /**
     * Returns the number of max allowed children.
     *
     * @return int
     */
    public function getMaxChildren()
    {
        return $this->_maxChildren;
    }

    /**
     * Set the priority of the current process.
     *
     * @param   int $priority
     * @param   int $processIdentifier
     * @return  Process Fluent interface, returns self
     */
    public function setPriority($priority, $processIdentifier = PRIO_PROCESS)
    {
        if (!is_int($priority) || $priority < -20 || $priority > 20) {
            $message = 'Invalid priority.';
            throw new \InvalidArgumentException($message);
        }

        if ($processIdentifier != PRIO_PROCESS
                || $processIdentifier != PRIO_PGRP
                || $processIdentifier != PRIO_USER) {
            $message = 'Invalid Process Identifier type.';
            throw new \InvalidArgumentException($message);
        }

        if (!pcntl_setpriority($priority, $this->_pid, $processIdentifier)) {
            $message = 'Unable to set the priority.';
            throw new \RuntimeException($message);
        }

        $this->_priority = $priority;
        return $this;
    }

    /**
     * Returns the priority of the current process.
     *
     * @return  int
     */
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     * Retursn the PID of the current process.
     *
     * @return  int
     */
    public function getPid()
    {
        return $this->_pid;
    }


}


