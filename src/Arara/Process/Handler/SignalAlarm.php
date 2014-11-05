<?php

namespace Arara\Process\Handler;

use Arara\Process\Action\Action;
use Arara\Process\Context;
use Arara\Process\Control;

class SignalAlarm extends SignalAbstract
{
    protected $action;
    protected $context;

    public function __construct(Control $control, Action $action, array $context)
    {
        $this->action = $action;
        $this->context = $context;

        parent::__construct($control);
    }

    public function __invoke($signal)
    {
        $this->context['finishTime'] = time();
        $this->action->trigger(Action::EVENT_TIMEOUT, $this->control, $this->context);
        $this->control->quit(3);
    }
}
