<?php

namespace Arara\Process\Control\Signal;

class TerminateHandler extends AbstractHandler
{
    public function __invoke($signal)
    {
        $this->control->quit(0);
    }
}
