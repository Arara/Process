<?php

namespace Arara\Process;

use Arara\Process\Action\Action;
use Arara\Process\Control\Status;
use ErrorException;
use Exception;
use RuntimeException;
use UnexpectedValueException;

class Child implements Process
{
    protected $processId;
    protected $startTime;
    protected $action;
    protected $control;
    protected $status;
    protected $timeout;
    protected $running = false;

    /**
     * Create a child process.
     *
     * @param  Action $action
     * @param  Control $control
     * @param  int[optional] $timeout
     */
    public function __construct(Action $action, Control $control, $timeout = 0)
    {
        $this->action = $action;
        $this->control = $control;
        $this->timeout = $timeout;
    }

    /**
     * Return the process id (PID).
     *
     * @return int
     */
    public function getId()
    {
        if (! $this->hasId()) {
            throw new UnexpectedValueException('There is no defined process identifier');
        }

        return $this->processId;
    }

    /**
     * Return TRUE if there is a defined id or FALSE if not.
     *
     * @return bool
     */
    public function hasId()
    {
        return (null !== $this->processId);
    }

    /**
     * Return the process status.
     *
     * @return Arara/Control/Status
     */
    public function getStatus()
    {
        if (! $this->status instanceof Status) {
            $this->wait();
        }

        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function isRunning()
    {
        if (! $this->hasId()) {
            return false;
        }

        if (! $this->running) {
            return false;
        }

        $this->running = $this->control->signal()->send(0, $this->getId());

        return $this->running;
    }

    /**
     * {@inheritDoc}
     */
    public function kill()
    {
        if (! $this->control->signal()->send(SIGKILL, $this->getId())) {
            throw new RuntimeException('Could not kill the process');
        }

        $this->running = false;
    }

    /**
     * Define the timeout handler.
     *
     * @return void
     */
    protected function setTimeoutHandler(array $context)
    {
        $action = $this->action;
        $control = $this->control;
        $control->signal()->handle('alarm', function () use ($action, $control, $context) {
            // @codeCoverageIgnoreStart
            $context['finishTime'] = time();
            $action->trigger(Action::EVENT_TIMEOUT, $control, $context);
            $control->quit(3);
            // @codeCoverageIgnoreEnd
        });
        $control->signal()->alarm($this->timeout);
    }

    /**
     * Overwrite default PHP error handler to throws exception when an error occurs.
     *
     * @return void
     */
    protected function setPhpErrorHandler()
    {
        set_error_handler(
            function ($severity, $message, $filename, $line) {
                throw new ErrorException($message, 0, $severity, $filename, $line);
            },
            E_ALL & ~E_NOTICE
        );
    }

    /**
     * Runs action trigger by the given event ignoring all exception.
     *
     * @return void
     */
    protected function runActionTrigger($event, array $context)
    {
        try {
            $this->action->trigger($event, $this->control, $context);
        } catch (Exception $exception) {
            // Pokémon Exception Handling
        }
    }

    /**
     * Execute the action, triggers the events and then exit the program.
     *
     * @return void
     */
    protected function run(array $context)
    {
        $this->runActionTrigger(Action::EVENT_START, $context);
        try {
            $event = $this->action->execute($this->control) ?: Action::EVENT_SUCCESS;
            $code = 0;
        } catch (ErrorException $errorException) {
            $context['exception'] = $errorException;
            $event = Action::EVENT_ERROR;
            $code = 2;
        } catch (Exception $exception) {
            $context['exception'] = $exception;
            $event = Action::EVENT_FAILURE;
            $code = 1;
        }
        $context['finishTime'] = time();
        $this->runActionTrigger($event, $context);
        $this->runActionTrigger(Action::EVENT_FINISH, $context);

        $this->control->quit($code);
    }

    /**
     * {@inheritDoc}
     */
    public function start()
    {
        if ($this->hasId()) {
            throw new RuntimeException('Process already started');
        }

        $this->running = true;
        $this->processId = $this->control->fork();
        if ($this->processId > 0) {
            usleep(5000); // Give time to the parent think
            return;
        }

        $this->processId = $this->control->info()->getId();

        $context = array(
            'processId' => $this->processId,
            'startTime' => time(),
            'timeout' => $this->timeout,
        );

        $this->setTimeoutHandler($context);
        $this->setPhpErrorHandler();
        $this->run($context);
        restore_error_handler();
    }

    /**
     * {@inheritDoc}
     */
    public function terminate()
    {
        if (! $this->control->signal()->send(SIGTERM, $this->getId())) {
            throw new RuntimeException('Could not terminate the process');
        }

        $this->running = false;
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        $waitStatus = 0;
        $waitReturn = $this->control->waitProcessId($this->getId(), $waitStatus);

        $this->status = new Status($waitStatus);

        if (-1 === $waitReturn) {
            throw new RuntimeException('An error occurred while waiting for the process');
        }
    }
}
