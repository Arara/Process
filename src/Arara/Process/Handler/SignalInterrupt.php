<?php

namespace Arara\Process\Handler;

/**
 * Handles the SIGINT signal.
 */
class SignalInterrupt extends SignalAbstract
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($signal)
    {
        $this->control->quit(3);
    }
}
