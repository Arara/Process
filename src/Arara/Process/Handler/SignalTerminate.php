<?php

namespace Arara\Process\Handler;

class SignalTerminate extends SignalAbstract
{
    public function __invoke($signal)
    {
        $this->control->quit(0);
    }
}
