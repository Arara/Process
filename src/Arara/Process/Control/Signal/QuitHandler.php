<?php

namespace Arara\Process\Control\Signal;

class QuitHandler extends AbstractHandler
{
    public function __invoke($signal)
    {
        $this->control->quit(4);
    }
}
