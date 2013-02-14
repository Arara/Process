<?php

namespace Arara\Process;

/**
 * Class to create forked process.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
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
    private $pid;

    /**
     * Process UID.
     *
     * @var int
     */
    private $uid;

    /**
     * Process GID.
     *
     * @var int
     */
    private $gid;

    /**
     * Contain the priority for the current process.
     *
     * @var int
     */
    private $priority = 0;

    /**
     * Callback to execute.
     *
     * @var mixed
     */
    private $callback;

    /**
     * Object that handles shared memory.
     *
     * @var Arara\Process\Memory
     */
    private $memory;

    /**
     * Constructor.
     *
     * Checks whether the system meets the requirements needed to run the class.
     */
    public function __construct()
    {
        // Shared memory object
        $this->memory = new Memory();
        $this->memory->write('__running', false);

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
        try {
            $this->stop();
        } catch (\Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Returns the callback of the process.
     *
     * @return  mixed
     */
    public function getCallback()
    {
        if (null === $this->callback) {
            $this->callback = function ()
            {
                // ..
            };
        }
        return $this->callback;
    }

    /**
     * Defines the callback to execute in the forked process.
     *
     * @param   mixed $callback
     * @return  Arara\Process\Fork Fluent interface, returns self.
     */
    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            $message = 'Callback given is not callable';
            throw new \InvalidArgumentException($message);
        }
        $this->callback = $callback;
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

        $pid = @pcntl_fork();

        if ($pid === -1) {
            $message = 'Unable to fork.';
            throw new \RuntimeException($message);
        } elseif ($pid > 0) {

            // We are in the parent process
            if (null !== $this->pid) {
                $message = 'Process already forked';
                throw new \UnexpectedValueException($message);
            }

            $this->pid = $pid;
            $this->memory->write('__running', true);

        } elseif ($pid === 0) {

            // We are in the child process
            posix_setgid($this->getGroupId());
            posix_setuid($this->getUserId());

            $groupId    = posix_getgid();
            $userId     = posix_getuid();

            if ($groupId != $this->getGroupId()
                    || $userId != $this->getUserId()) {
                $message = sprintf(
                    'Unable to fork process as "%d:%d". "%d:%d" given',
                    $this->getUserId(),
                    $this->getGroupId(),
                    $userId,
                    $groupId
                );
                throw new \RuntimeException($message);
            }

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

            try {

                $result = call_user_func($this->getCallback());
                $status = self::RESULT_STATUS_SUCESS;

            } catch (\ErrorException $exception) {

                $result = $exception->getMessage();
                $status = self::RESULT_STATUS_FAIL;

            } catch (\Exception $exception) {

                $result = $exception->getMessage();
                $status = self::RESULT_STATUS_ERROR;

            }
            
            restore_error_handler();

            $this->memory->write('__result', $result);
            $this->memory->write('__status', $status);
            $this->memory->write('__running', false);
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
        if ($this->pid > 0) {
            posix_kill($this->pid, SIGKILL);
        }
        $this->pid = null;
        $this->memory->clean();
    }

    /**
     * Returns TRUE if the forked process is running or FALSE if not.
     *
     * @return  bool
     */
    public function isRunning()
    {
        return $this->memory->read('__running');
    }

    /**
     * Returns the callback result.
     *
     * @return  mixed
     */
    public function getCallbackResult()
    {
        return $this->memory->read('__result');
    }

    /**
     * Returns the callback result.
     *
     * @return  int
     */
    public function getCallbackStatus()
    {
        return $this->memory->read('__status');
    }

    /**
     * Returns TRUE if the callback is successful.
     *
     * @return  bool
     */
    public function isCallbackSuccessful()
    {
        return ($this->memory->read('__status') == self::RESULT_STATUS_SUCESS);
    }

    /**
     * Add a new signal that will be called to the given function with
     * an optional callback.
     *
     * @param   int $signal
     * @param   string|array|Clousure $callback
     * @return  Arara\Process\Fork Fluent interface, returns self.
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
                $this->memory->clean();
                exit(0);
                break;
            case SIGQUIT: // Quit
            case SIGINT:  // Stop from the keyboard
            case SIGKILL: // Kill
                $this->memory->clean();
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
     * @return  Arara\Process\Process\Fork Fluent interface, returns self
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

        if (!pcntl_setpriority($priority, $this->pid, $processIdentifier)) {
            $message = 'Unable to set the priority.';
            throw new \RuntimeException($message);
        }

        $this->priority = $priority;
        return $this;
    }

    /**
     * Returns the priority of the current process.
     *
     * @return  int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Retursn the PID of the current process.
     *
     * @return  int|null
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Defines the process UNIX User ID.
     *
     * @throws  InvalidArgumentException If the UID is not valid.
     * @param   int $value
     * @return  Arara\Process\Fork Fluent interface, returns self
     */
    public function setUserId($value)
    {
        if (false === posix_getpwuid($value)) {
            $message = sprintf('The given UID "%s" is not valid', $value);
            throw new \InvalidArgumentException($message);
        }
        $this->uid = $value;
        return $this;
    }

    /**
     * Returns the process UNIX User ID.
     *
     * @return  int
     */
    public function getUserId()
    {
        if (null === $this->uid) {
            $this->uid = posix_getuid();
        }
        return $this->uid;
    }

    /**
     * Defines the process UNIX Group ID.
     *
     * @param   int $value
     * @return  Arara\Process\Fork Fluent interface, returns self
     */
    public function setGroupId($value)
    {
        if (false === posix_getgrgid($value)) {
            $message = sprintf('The given GID "%s" is not valid', $value);
            throw new \InvalidArgumentException($message);
        }
        $this->gid = $value;
        return $this;
    }

    /**
     * Returns the process UNIX Group ID.
     *
     * @return  int
     */
    public function getGroupId()
    {
        if (null === $this->gid) {
            $this->gid = posix_getgid();
        }
        return $this->gid;
    }


}

