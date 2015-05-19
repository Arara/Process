<?php
/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process;

use Arara\Process\Action\Action;
use Arara\Process\Control\Status;
use Arara\Process\Exception\ErrorException;
use Arara\Process\Exception\RuntimeException;
use Arara\Process\Exception\UnexpectedValueException;
use Arara\Process\Handler\ErrorException as PhpError;
use Arara\Process\Handler\SignalAlarm;
use Exception;

/**
 * Handle child process.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Child implements Process
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Control
     */
    protected $control;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Create a child process.
     *
     * Defines internal instances and trigger Action::EVENT_INIT event.
     *
     * @param Action  $action
     * @param Control $control
     * @param integer $timeout
     */
    public function __construct(Action $action, Control $control, $timeout = 0)
    {
        $this->action = $action;
        $this->control = $control;
        $this->context = new Context();
        $this->context->isRunning = false;
        $this->context->processId = null;
        $this->context->timeout = $timeout;

        $action->trigger(Action::EVENT_INIT, $this->control, $this->context);
    }

    /**
     * Return the process id (PID).
     *
     * @return integer
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
     * @return boolean
     */
    public function hasId()
    {
        return (null !== $this->context->processId);
    }

    /**
     * Return the process status.
     *
     * @return Status
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
        return ($this->context->isRunning = $this->sendSignal(0));
    }

    /**
     * Sends a signal to the current process and returns its results.
     *
     * @param integer $signalNumber
     *
     * @return boolean
     */
    protected function sendSignal($signalNumber)
    {
        if (! $this->context->isRunning || ! $this->context->processId) {
            return false;
        }

        $result = $this->control->signal()->send($signalNumber, $this->context->processId);
        if (in_array($signalNumber, array(SIGTERM, SIGKILL))) {
            $this->context->isRunning = false;
            $this->context->processId = null;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function kill()
    {
        return $this->sendSignal(SIGKILL);
    }

    /**
     * Define the timeout handler.
     *
     * @return null
     */
    protected function setHandlerAlarm()
    {
        $handler = new SignalAlarm($this->control, $this->action, $this->context);
        $this->control->signal()->setHandler('alarm', $handler);
        $this->control->signal()->alarm($this->context->timeout);
    }

    /**
     * Overwrite default PHP error handler to throws exception when an error occurs.
     *
     * @return null
     */
    protected function setHandlerErrorException()
    {
        $handler = new PhpError();

        set_error_handler($handler, E_ALL & ~E_NOTICE);
    }

    /**
     * Runs action trigger by the given event ignoring all exception.
     *
     * @return null
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
     * @return null
     */
    protected function run()
    {
        $this->silentRunActionTrigger(Action::EVENT_START);
        try {
            $event = $this->action->execute($this->control, $this->context) ?: Action::EVENT_SUCCESS;
            $this->context->exitCode = 0;
        } catch (ErrorException $errorException) {
            $event = Action::EVENT_ERROR;
            $this->context->exception = $errorException;
            $this->context->exitCode = 2;
        } catch (Exception $exception) {
            $event = Action::EVENT_FAILURE;
            $this->context->exception = $exception;
            $this->context->exitCode = 1;
        }
        $this->context->finishTime = time();
        $this->silentRunActionTrigger($event);
        $this->silentRunActionTrigger(Action::EVENT_FINISH);
    }

    /**
     * {@inheritDoc}
     */
    public function start()
    {
        if ($this->hasId() && $this->isRunning()) {
            throw new RuntimeException('Process already started');
        }

        $this->context->processId = $this->control->fork();
        $this->context->isRunning = true;
        if ($this->context->processId > 0) {
            $this->action->trigger(Action::EVENT_FORK, $this->control, $this->context);

            return;
        }

        $this->context->processId = $this->control->info()->getId();
        $this->context->startTime = time();

        $this->setHandlerAlarm();
        $this->setHandlerErrorException();
        $this->run();
        restore_error_handler();

        $this->control->quit($this->context->exitCode);
    }

    /**
     * {@inheritDoc}
     */
    public function terminate()
    {
        return $this->sendSignal(SIGTERM);
    }

    /**
     * {@inheritDoc}
     */
    public function wait()
    {
        if (!is_null($this->status)) {
            return false;
        }

        $waitStatus = 0;
        $waitReturn = $this->control->waitProcessId($this->getId(), $waitStatus);
        if ($waitReturn === $this->getId()) {
            $this->context->isRunning = false;
            $this->context->processId = null;
        }

        $this->status = new Status($waitStatus);

        return (-1 !== $waitReturn);
    }
}
