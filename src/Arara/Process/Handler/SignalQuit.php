<?php

namespace Arara\Process\Handler;

class SignalQuit extends SignalAbstract
{
    public function __invoke($signal)
    {
        $this->control->quit(4);
    }
}
