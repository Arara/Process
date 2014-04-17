<?php

namespace Arara\Process\Control\Signal;

class InterruptHandler extends AbstractHandler
{
    public function __invoke($signal)
    {
        $this->control->quit(3);
    }
}
