<?php

namespace Arara\Process\Handler;

/**
 * Handles the SIGQUIT signal.
 */
class SignalQuit extends SignalAbstract
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($signal)
    {
        $this->control->quit(4);
    }
}
