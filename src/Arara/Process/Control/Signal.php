<?php

namespace Arara\Process\Control;

use InvalidArgumentException;

class Signal
{
    protected $signals = array(
        'alarm' => SIGALRM,
        'child' => SIGCHLD,
        'hangup' => SIGHUP,
        'interrupt' => SIGINT,
        'kill' => SIGKILL,
        'pipe' => SIGPIPE,
        'quit' => SIGQUIT,
        'stop' => SIGSTOP,
        'terminate' => SIGTERM,
    );

    /**
     * Define the time (in seconds) to send an alarm to the current process.
     *
     * @link   http://php.net/pcntl_alarm
     * @param  int $seconds Time (in seconds) to send an alarm.
     * @return int
     */
    public function alarm($seconds)
    {
        return pcntl_alarm($seconds);
    }

    /**
     * Calls signal handlers for pending signals.
     *
     * @link   http://php.net/pcntl_signal_dispatch
     * @return bool
     */
    public function dispatch()
    {
        return pcntl_signal_dispatch();
    }

    /**
     * Define a handler for the given signal.
     *
     * @link   http://php.net/pcntl_signal
     * @throws InvalidArgumentException When $handler is not a valid callback.
     * @param  int|string $signal Signal (code or name) to handle.
     * @param  callable $handler Callback to handle the given signal.
     * @return bool
     */
    public function handle($signal, $handler)
    {
        if (! is_callable($handler)) {
            throw new InvalidArgumentException('The given handler is not a valid callback');
        }

        return pcntl_signal($this->translateSignal($signal), $handler);
    }

    /**
     * Ignore (do not handle) the given signal.
     *
     * Not only user defined handlers, but there are default handlers for a good
     * part of existing signals.
     *
     * @link   http://php.net/pcntl_signal
     * @param  int|string $signal Signal (code or name) to ignore.
     * @return bool
     */
    public function ignore($signal)
    {
        return pcntl_signal($this->translateSignal($signal), SIG_IGN);
    }

    /**
     * Send a signal to a process.
     *
     * @link   http://php.net/posix_kill
     * @param  int|string $signal Signal (code or name) to send.
     * @param  int[optional] $processId Process id to send signal, if not defined will use the current one.
     * @return bool
     */
    public function send($signal, $processId = null)
    {
        if (null === $processId) {
            $processId = posix_getpid();
        }

        return posix_kill($processId, $this->translateSignal($signal));
    }

    /**
     * Translate signals names to codes.
     *
     * @param  mixed $signal Signal name, PCNTL constant name or PCNTL constant value.
     * @return int
     */
    protected function translateSignal($signal)
    {
        if (isset($this->signals[$signal])) {
            return $this->signals[$signal];
        }

        if (defined($signal)) {
            return constant($signal);
        }

        return $signal;
    }
}
