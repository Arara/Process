<?php

namespace Arara\Process\Handler;

class SignalChild extends SignalAbstract
{
    public function __invoke($signal)
    {
        $status = 0;
        while ($this->control->wait($status, (WNOHANG | WUNTRACED)) > 0) {
            usleep(1000);
        }
    }
}
