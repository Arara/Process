<?php
/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;

/**
 * Interface for process actions.
 *
 * Classes based on this interface must have two responsibilities.
 * 1. Run an action for a given process;
 * 2. Run triggers when some events are dispatched.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
interface Action
{
    /**
     * Must be triggered when action is initialized.
     *
     * This event will always be triggered when the action is attached to a Child object.
     */
    const EVENT_INIT = 128;

    /**
     * Must be triggered when action is forked.
     *
     * After the action is forked it is triggered on the **parent** process.
     */
    const EVENT_FORK = 256;

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
     * @param Control $control Process controller.
     * @param Context $context Process context.
     *
     * @return integer Event status.
     */
    public function execute(Control $control, Context $context);

    /**
     * Must be called after action is finished to trigger possible defined events.
     *
     * @param integer $event   Event to be triggered.
     * @param Control $control Process controller.
     * @param Context $context Process context.
     *
     * @return null No return value is expected.
     */
    public function trigger($event, Control $control, Context $context);
}
