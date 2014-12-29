<?php

namespace Arara\Process\Handler;

use Arara\Process\Action\Action;
use Arara\Process\Context;
use Arara\Process\Control;

/**
 * Handles the SIGALRM signal.
 */
class SignalAlarm extends SignalAbstract
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
     * @param Control $control
     * @param Action  $action
     * @param Context $context
     */
    public function __construct(Control $control, Action $action, Context $context)
    {
        $this->action = $action;
        $this->context = $context;

        parent::__construct($control);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke($signal)
    {
        $this->context->exitCode = 3;
        $this->context->finishTime = time();
        $this->action->trigger(Action::EVENT_TIMEOUT, $this->control, $this->context);
        $this->control->quit($this->context->exitCode);
    }
}
