<?php

namespace Arara\Process\Control\Signal;

class ChildHandler extends AbstractHandler
{
    public function __invoke($signal)
    {
        $status = 0;
        while ($this->control->wait($status, (WNOHANG | WUNTRACED)) > 0) {
            usleep(1000);
        }
    }
}
