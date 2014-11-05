<?php

namespace Arara\Process\Handler;

use Arara\Process\Control;

abstract class SignalAbstract
{
    protected $control;

    public function __construct(Control $control)
    {
        $this->control = $control;
    }

    abstract public function __invoke($signal);
}
