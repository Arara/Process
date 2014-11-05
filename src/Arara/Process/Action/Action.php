<?php

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;

/**
 * Interface for process actions.
 *
 * Classes based on this interface must have two responsibilities.
 * 1. Run an action for a given process;
 * 2. Run triggers when some events are dispatched.
 */
interface Action
{
    /**
     * Must be triggered when action starts.
     *
     * This event will always be triggered before the execute() method be executed.
     */
    const EVENT_START = 2;

    /**
     * Must be triggered when action is finished with success.
     *
     * We consider an error when:
     * - Action does not get a PHP error
     * - Action does not throws an exception
     * - Action does not return any value
     * - Action returns EVENT_SUCCESS value
     */
    const EVENT_SUCCESS = 4;

    /**
     * Must be triggered when action is finished with an error.
     *
     * We consider an error when:
     * - Action get a PHP error
     * - Action returns EVENT_ERROR value.
     */
    const EVENT_ERROR = 8;

    /**
     * Must be triggered when action is finished with a failure.
     *
     * We consider a failure when:
     * - Action throws an exception;
     * - Action returns EVENT_FAILURE value.
     */
    const EVENT_FAILURE = 16;

    /**
     * Must be triggered when action get a timeout.
     */
    const EVENT_TIMEOUT = 32;

    /**
     * Must be triggered when action finish.
     *
     * This event will always be triggered after the execute() method be executed.
     */
    const EVENT_FINISH = 64;

    /**
     * This is the action to be runned.
     *
     * @param  Control $control Process controller.
     * @param  Context $context Process context.
     * @return int Event status.
     */
    public function execute(Control $control, Context $context);

    /**
     * Must be called after action is finished to trigger possible defined events.
     *
     * @param  int $event Event to be triggered.
     * @param  Control $control Process controller.
     * @param  Context $context Process context.
     * @return void No return value is expected.
     */
    public function trigger($event, Control $control, Context $context);
}
