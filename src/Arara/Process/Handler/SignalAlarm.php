<?php
/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Handler;

use Arara\Process\Action\Action;
use Arara\Process\Context;
use Arara\Process\Control;

/**
 * Handles the SIGALRM signal.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
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
