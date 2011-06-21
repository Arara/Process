<?php

/**
 * @namespace
 */
namespace PHProcess\Console\Process;

/**
 * Class to create forked process.
 *
 * @category   PHProcess
 * @package    PHProcess\Console
 * @subpackage PHProcess\Console\Process
 * @author     Henrique Moody <henriquemoody@gmail.com>
 */
class Fork
{

    /**
     * Represents the success status of the callback.
     */
    const RESULT_STATUS_SUCESS  = 1;

    /**
     * Represents the error status of the callback.
     */
    const RESULT_STATUS_ERROR   = 2;

    /**
     * Represents the fail status of the callback.
     *
     * This error may happen when PHP generates an error.
     */
    const RESULT_STATUS_FAIL    = 3;

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
     * @var PHProcess\Console\Process\Memory
     */
    private $_memory;

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
            throw new \UnexpectedValueException($message);

        } else if (!function_exists('posix_setgid')) {
            $message = 'posix_* functions are required';
            throw new \UnexpectedValueException($message);

        }

        // Shared memory object
        $this->_memory = new Memory();
        $this->_memory->write('__running', false);

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
        if ($this->_pid > 0) {
            posix_kill($this->_pid, SIGKILL);
        }
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
     * @return  PHProcess\Console\Process\Fork Fluent interface, returns self.
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

            $this->_pid = $pid;
            $this->_memory->write('__running', true);

        } elseif ($pid === 0) {

            // We are in the child process
            posix_setgid($this->getGroupId());
            posix_setuid($this->getUserId());

            $groupId    = posix_getgid();
            $userId     = posix_getuid();

            if ($groupId != $this->getGroupId()
                    || $userId != $this->getUserId()) {
                $message = sprintf(
                    'Unable to fork process as UID:GID "%d:%d". "%d:%d" given',
                    $this->getUserId(),
                    $this->getGroupId(),
                    $userId,
                    $groupId
                );
                throw new \RuntimeException($message);
            }

            try {

                // Custom error hanlder
                set_error_handler(
                    function ($severity, $message, $filename, $line) {
                        throw new \ErrorException(
                            $message,
                            0,
                            $severity,
                            $filename,
                            $line
                        );
                    }
                );

                $result = call_user_func($this->getCallback());
                $status = self::RESULT_STATUS_SUCESS;

            } catch (\ErrorException $exception) {

                $result = $exception->getMessage();
                $status = self::RESULT_STATUS_FAIL;

            } catch (\Exception $exception) {

                $result = $exception->getMessage();
                $status = self::RESULT_STATUS_ERROR;

            }

            $this->_memory->write('__result', $result);
            $this->_memory->write('__status', $status);
            $this->_memory->write('__running', false);
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
        if ($this->_pid > 0) {
            posix_kill($this->_pid, SIGKILL);
        }
        $this->_memory->clean();
    }

    /**
     * Returns TRUE if the forked process is running or FALSE if not.
     *
     * @return  bool
     */
    public function isRunning()
    {
        return $this->_memory->read('__running');
    }

    /**
     * Returns the callback result.
     *
     * @return  mixed
     */
    public function getCallbackResult()
    {
        return $this->_memory->read('__result');
    }

    /**
     * Returns the callback result.
     *
     * @return  int
     */
    public function getCallbackStatus()
    {
        return $this->_memory->read('__status');
    }

    /**
     * Returns TRUE if the callback is successful.
     *
     * @return  bool
     */
    public function isCallbackSuccessful()
    {
        return ($this->_memory->read('__status') == self::RESULT_STATUS_SUCESS);
    }

    /**
     * Add a new signal that will be called to the given function with
     * an optional callback.
     *
     * @param   int $signal
     * @param   string|array|Clousure $callback
     * @return  PHProcess\Console\Process\Fork Fluent interface, returns self.
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
            case SIGCHLD:
                // zombies nevermore!
                while (pcntl_wait($status, WNOHANG | WUNTRACED) > 0) {
                    usleep(1000);
                }
                break;
        }
    }

    /**
     * Set the priority of the current process.
     *
     * @param   int $priority
     * @param   int $processIdentifier
     * @return  PHProcess\Console\Process\Fork Fluent interface, returns self
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

    /**
     * Defines the process UNIX User ID.
     *
     * @throws  InvalidArgumentException If the UID is not valid.
     * @param   int $value
     * @return  PHProcess\Console\Process\Fork Fluent interface, returns self
     */
    public function setUserId($value)
    {
        if (false === posix_getpwuid($value)) {
            $message = sprintf('The given UID "%s" is not valid', $value);
            throw new \InvalidArgumentException($message);
        }
        $this->_uid = $value;
        return $this;
    }

    /**
     * Returns the process UNIX User ID.
     *
     * @return  int
     */
    public function getUserId()
    {
        if (null === $this->_uid) {
            $this->_uid = posix_getuid();
        }
        return $this->_uid;
    }

    /**
     * Defines the process UNIX Group ID.
     *
     * @param   int $value
     * @return  PHProcess\Console\Process\Fork Fluent interface, returns self
     */
    public function setGroupId($value)
    {
        if (false === posix_getgrgid($value)) {
            $message = sprintf('The given GID "%s" is not valid', $value);
            throw new \InvalidArgumentException($message);
        }
        $this->_gid = $value;
        return $this;
    }

    /**
     * Returns the process UNIX Group ID.
     *
     * @return  int
     */
    public function getGroupId()
    {
        if (null === $this->_gid) {
            $this->_gid = posix_getgid();
        }
        return $this->_gid;
    }


}

