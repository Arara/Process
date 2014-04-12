<?php

namespace Arara\Process\Action;

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
     * Be careful: This action will be always run before the execute() method.
     */
    const EVENT_START = 2;

    /**
     * Must be triggered when action is finished with success.
     *
     * We consider an error when:
     * - Action returns EVENT_SUCCESS value;
     * - Action does not return any value;
     * - Action does not throws an exception.
     */
    const EVENT_SUCCESS = 4;

    /**
     * Must be triggered when action is finished with an error.
     *
     * We consider an error when:
     * - Action throws an exception;
     * - Action does not return EVENT_SUCCESS value;
     * - Action returns EVENT_ERROR value.
     */
    const EVENT_ERROR = 8;

    /**
     * Must be triggered when action is finished with an error.
     *
     * We consider a failure when:
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
     * Be careful: This action will be always run bofore the child finish, whatever the current event is.
     */
    const EVENT_FINISH = 64;

    /**
     * This is the action to be runned.
     *
     * @param  Control $control Process controller.
     * @return int Event status.
     */
    public function execute(Control $control);

    /**
     * Must be called after action is finished to trigger possible defined events.
     *
     * @param  int $event Event to be triggered.
     * @param  Control $control Process controller.
     * @param  array $control Event context.
     * @return void No return value is expected.
     */
    public function trigger($event, Control $control, array $context);
}
