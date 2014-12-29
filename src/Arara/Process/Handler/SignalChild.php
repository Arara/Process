<?php

namespace Arara\Process\Handler;

/**
 * Handles the SIGCHLD signal.
 */
class SignalChild extends SignalAbstract
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($signal)
    {
        $status = 0;
        while ($this->control->wait($status, (WNOHANG | WUNTRACED)) > 0) {
            usleep(1000);
        }
    }
}
