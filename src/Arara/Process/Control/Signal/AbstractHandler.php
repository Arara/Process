<?php

namespace Arara\Process\Control\Signal;

use Arara\Process\Control;

abstract class AbstractHandler
{
    protected $control;

    public function __construct(Control $control)
    {
        $this->control = $control;
    }

    abstract public function __invoke($signal);
}
