<?php

namespace Arara\Process\Handler;

use Arara\Process\Control;

/**
 * Abstract implementation for signal handlers.
 */
abstract class SignalAbstract
{
    /**
     * @var Control
     */
    protected $control;

    /**
     * @param Control $control
     */
    public function __construct(Control $control)
    {
        $this->control = $control;
    }

    /**
     * Handler for the signal.
     *
     * @param integer $signal
     *
     * @return null
     */
    abstract public function __invoke($signal);
}
