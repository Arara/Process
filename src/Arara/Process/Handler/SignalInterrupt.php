<?php

namespace Arara\Process\Handler;

class SignalInterrupt extends SignalAbstract
{
    public function __invoke($signal)
    {
        $this->control->quit(3);
    }
}
