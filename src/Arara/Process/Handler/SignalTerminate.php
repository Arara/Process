<?php

namespace Arara\Process\Handler;

/**
 * Handles the SIGTERM signal.
 */
class SignalTerminate extends SignalAbstract
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($signal)
    {
        $this->control->quit(0);
    }
}
