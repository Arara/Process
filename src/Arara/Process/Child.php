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
    protected $action;
    protected $context;
    protected $control;
    protected $status;

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
        $this->context = new Context();
        $this->context->isRunning = false;
        $this->context->processId = null;
        $this->context->timeout = $timeout;
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

        return $this->context->processId;
    }

    /**
     * Return TRUE if there is a defined id or FALSE if not.
     *
     * @return bool
     */
    public function hasId()
    {
        return (null !== $this->context->processId);
    }

    /**
     * Return the process status.
     *
     * @return Arara\Control\Status
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

        if (! $this->context->isRunning) {
            return false;
        }

        $this->context->isRunning = $this->control->signal()->send(0, $this->getId());

        return $this->context->isRunning;
    }

    /**
     * {@inheritDoc}
     */
    public function kill()
    {
        if (! $this->control->signal()->send(SIGKILL, $this->getId())) {
            throw new RuntimeException('Could not kill the process');
        }

        $this->context->isRunning = false;
    }

    /**
     * Define the timeout handler.
     *
     * @return void
     */
    protected function setHandlerAlarm()
    {
        $handler = new Handler\SignalAlarm($this->control, $this->action, $this->context);
        $this->control->signal()->handle('alarm', $handler);
        $this->control->signal()->alarm($this->context->timeout);
    }

    /**
     * Overwrite default PHP error handler to throws exception when an error occurs.
     *
     * @return void
     */
    protected function setHandlerErrorException()
    {
        set_error_handler(new Handler\ErrorException(), E_ALL & ~E_NOTICE);
    }

    /**
     * Runs action trigger by the given event ignoring all exception.
     *
     * @return void
     */
    protected function silentRunActionTrigger($event)
    {
        try {
            $this->action->trigger($event, $this->control, $this->context);
        } catch (Exception $exception) {
            // Pokémon Exception Handling
        }
    }

    /**
     * Execute the action, triggers the events and then exit the program.
     *
     * @return void
     */
    protected function run()
    {
        $this->silentRunActionTrigger(Action::EVENT_START);
        try {
            $this->context->event = $this->action->execute($this->control) ?: Action::EVENT_SUCCESS;
            $this->context->exitCode = 0;
        } catch (ErrorException $errorException) {
            $this->context->event = Action::EVENT_ERROR;
            $this->context->exception = $errorException;
            $this->context->exitCode = 2;
        } catch (Exception $exception) {
            $this->context->event = Action::EVENT_FAILURE;
            $this->context->exception = $exception;
            $this->context->exitCode = 1;
        }
        $this->context->finishTime = time();
        $this->silentRunActionTrigger($this->context->event);
        $this->silentRunActionTrigger(Action::EVENT_FINISH);

        $this->control->quit($this->context->exitCode);
    }

    /**
     * {@inheritDoc}
     */
    public function start()
    {
        if ($this->hasId()) {
            throw new RuntimeException('Process already started');
        }

        $processId = $this->control->fork();
        if ($processId > 0) {
            $this->context->isRunning = true;
            $this->context->processId = $processId;
            usleep(5000); // Give time to the parent think
            return;
        }

        $this->context->isRunning = true;
        $this->context->processId = $this->control->info()->getId();
        $this->context->startTime = time();

        $this->setHandlerAlarm();
        $this->setHandlerErrorException();
        $this->run();
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

        $this->context->isRunning = false;
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
