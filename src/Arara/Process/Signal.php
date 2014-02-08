<?php

namespace Arara\Process;

class Signal
{
    public function __construct()
    {
        declare(ticks = 1);
    }

    public function setDefaultHandlers()
    {
        $this->handle(SIGINT, array($this, 'defaultHandler'));
        $this->handle(SIGQUIT, array($this, 'defaultHandler'));
        $this->handle(SIGTERM, array($this, 'defaultHandler'));
        $this->handle(SIGCHLD, array($this, 'defaultHandler'));
    }

    public function handle($signal, $handler)
    {
        pcntl_signal($signal, $handler);
    }

    public function defaultHandler($signal)
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
