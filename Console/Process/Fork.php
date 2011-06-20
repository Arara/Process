<?php

/**
 * @namespace
 */
namespace Console\Process;

/**
 * Class to create forked process.
 *
 * @package     Console
 * @subpackage  Console\Process
 * @author      Henrique Moody <henriquemoody@gmail.com>
 */
class Fork
{

    /**
     * Contain the PID of the child process.
     *
     * @var int
     */
    private $_pid;

    /**
     * Process UID.
     *
     * @var int
     */
    private $_uid;

    /**
     * Process GID.
     *
     * @var int
     */
    private $_gid;

    /**
     * Contain the priority for the current process.
     *
     * @var int
     */
    private $_priority = 0;

    /**
     * Callback to execute.
     *
     * @var mixed
     */
    private $_callback;

    /**
     * Object that handles shared memory.
     *
     * @var Console\Process\Memory
     */
    private $_memory;

    /**
     * Constructor.
     *
     * Checks whether the system meets the requirements needed to run the class.
     */
    public function __construct($uid = null, $gid = null)
    {
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $message = 'Cannot run on windows';
            throw new \UnexpectedValueException($message);

        } else if (!in_array(substr(PHP_SAPI, 0, 3), array('cli', 'cgi'))) {
            $message = 'Can only run on CLI or CGI enviroment';
            throw new \UnexpectedValueException($message);

        } else if (!function_exists('pcntl_fork')) {
            $message = 'pcntl_* functions are required';
            throw new \UnexpectedValueException($message);

        } else if (!function_exists('posix_setgid')) {
            $message = 'posix_* functions are required';
            throw new \UnexpectedValueException($message);

        }

        if (null === $uid) {
            $uid = posix_getuid();
        }
        $this->_uid = (int) $uid;

        if (null === $gid) {
            $gid = posix_getgid();
        }
        $this->_gid = (int) $gid;

        // Shared memory object
        $this->_memory = new Memory();
        $this->_memory->write('running', false);

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
        pcntl_waitpid($this->_pid, $status);
    }

    /**
     * Returns the callback of the process.
     *
     * @return  mixed
     */
    public function getCallback()
    {
        if (null === $this->_callback) {
            $this->_callback = function () 
            {
                // ..
            };
        }
        return $this->_callback;
    }

    /**
     * Defines the callback to execute in the forked process.
     *
     * @param   mixed $callback
     * @return  Console\Process\Fork Fluent interface, returns self.
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            $message = 'Callback given is not callable';
            throw new \InvalidArgumentException($message);
        }
        $this->_callback = $callback;
        return $this;
    }

    /**
     * Starts the forked process.
     *
     * @return  void
     */
    public function start()
    {
        $this->addSignal(SIGCHLD, array($this, 'signalHandler'));

        $pid = pcntl_fork();

        if ($pid === -1) {
            $message = 'Unable to fork.';
            throw new \RuntimeException($message);
        } elseif ($pid > 0) {

            // We are in the parent process
            if (null !== $this->_pid) {
                $message = 'Process already forked';
                throw new \UnexpectedValueException($message);
            }

            $this->_memory->write('running', true);
            $this->_pid = $pid;

        } elseif ($pid === 0) {

            posix_setgid($this->getGid());
            posix_setuid($this->getUid());

            // We are in the child process
            call_user_func($this->getCallback());
            $this->_memory->write('running', false);
            exit(0);
        }
    }

    /**
     * Stop the forked process.
     *
     * Kill the process.
     *
     * @return  void
     */
    public function stop()
    {
        if (null === $this->_pid) {
            $message = 'There is no forked process.';
            throw new \UnexpectedValueException($message);
        }
        posix_kill($this->_pid, SIGKILL);
    }

    /**
     * Returns TRUE if the forked process is running or FALSE if not.
     *
     * @return  bool
     */
    public function isRunning()
    {
        return $this->_memory->read('running');
    }

    /**
     * Add a new signal that will be called to the given function with
     * an optional callback.
     *
     * @param   int $signal
     * @param   string|array|Clousure $callback
     * @return  Console\Process\Fork Fluent interface, returns self.
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
     * Set the priority of the current process.
     *
     * @param   int $priority
     * @param   int $processIdentifier
     * @return  Console\Process\Fork Fluent interface, returns self
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
            $message = 'Invalid Console\Process\Fork Identifier type.';
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

    /**
     * Returns the process UID
     *
     * @return  int
     */
    public function getUid()
    {
        return $this->_uid;
    }

    /**
     * Returns the process GID.
     *
     * @return  int
     */
    public function getGid()
    {
        return $this->_gid;
    }


}

