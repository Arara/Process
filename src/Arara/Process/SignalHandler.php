<?php

namespace Arara\Process;

class SignalHandler
{

    public function __construct()
    {
        pcntl_signal(SIGINT, array($this, 'handle'));
        pcntl_signal(SIGQUIT, array($this, 'handle'));
        pcntl_signal(SIGTERM, array($this, 'handle'));
        pcntl_signal(SIGCHLD, array($this, 'handle'));
    }

    public function handle($signal)
    {
        switch ($signal) {
            case SIGINT:
            case SIGQUIT:
            case SIGKILL:
                $this->quit(1);
                break;

            case SIGTERM:
                $this->quit(0);
                break;

            case SIGCHLD:
                $status = 0;
                while (pcntl_wait($status, WNOHANG | WUNTRACED) > 0) {
                    usleep(1000);
                }
                break;
        }

        return $this;
    }

    // @codeCoverageIgnoreStart
    public function quit($code)
    {
        exit($code);
    }
    // @codeCoverageIgnoreEnd

}
