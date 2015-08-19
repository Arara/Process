<?php

/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Control;

use Arara\Process\Exception\InvalidArgumentException;
use Arara\Process\Exception\RuntimeException;

/**
 * Process signal controller.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Signal
{
    /**
     * List of signals by name.
     *
     * @var array
     */
    protected $signals = [
        'abort'     => SIGABRT,
        'alarm'     => SIGALRM,
        'child'     => SIGCHLD,
        'continue'  => SIGCONT,
        'hangup'    => SIGHUP,
        'interrupt' => SIGINT,
        'kill'      => SIGKILL,
        'pipe'      => SIGPIPE,
        'quit'      => SIGQUIT,
        'stop'      => SIGSTOP,
        'suspend'   => SIGTSTP,
        'terminate' => SIGTERM,
    ];

    /**
     * May contain signals handlers.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Define the time (in seconds) to send an alarm to the current process.
     *
     * @param int $seconds Time (in seconds) to send an alarm.
     *
     * @return int
     */
    public function alarm($seconds)
    {
        return pcntl_alarm($seconds);
    }

    /**
     * Calls signal handlers for pending signals.
     *
     *
     * @return bool
     */
    public function dispatch()
    {
        return pcntl_signal_dispatch();
    }

    /**
     * Register a signal handler.
     *
     *
     * @param int          $signalNumber Signal number.
     * @param callable|int $handler      The signal handler.
     *
     * @throws RuntimeException When can not register handler.
     */
    protected function registerHandler($signalNumber, $handler)
    {
        if (pcntl_signal($signalNumber, $handler)) {
            return;
        }
        throw new RuntimeException('Could not define signal handler');
    }

    /**
     * Define a handler for the given signal.
     *
     * @param string|int   $signal    Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|int $handler   The signal handler.
     * @param string       $placement Placement of handler ("set", "append" or "prepend").
     */
    protected function handle($signal, $handler, $placement)
    {
        declare (ticks = 1);

        $signalNumber = $this->translateSignal($signal);

        if (is_int($handler) && in_array($handler, [SIG_IGN, SIG_DFL])) {
            unset($this->handlers[$signalNumber]);
            $this->registerHandler($signalNumber, $handler);

            return;
        }

        $this->placeHandler($signalNumber, $handler, $placement);
    }

    /**
     * Define a callback handler for the given signal.
     *
     * @param int      $signalNumber Signal number.
     * @param callable $handler      The signal handler.
     * @param string   $placement    Placement of handler ("set", "append" or "prepend").
     */
    protected function placeHandler($signalNumber, callable $handler, $placement)
    {
        if (! isset($this->handlers[$signalNumber])) {
            $this->handlers[$signalNumber] = [];
            $this->registerHandler($signalNumber, $this);
        }

        switch ($placement) {
            case 'set':
                $this->handlers[$signalNumber] = [$handler];
                break;

            case 'append':
                array_push($this->handlers[$signalNumber], $handler);
                break;

            case 'prepend':
                array_unshift($this->handlers[$signalNumber], $handler);
                break;
        }
    }

    /**
     * Returns handlers of a specific signal.
     *
     * @param int|string $signal
     *
     * @return array
     */
    public function getHandlers($signal)
    {
        $signalNumber = $this->translateSignal($signal);
        $handlers = [];
        if (isset($this->handlers[$signalNumber])) {
            $handlers = $this->handlers[$signalNumber];
        }

        return $handlers;
    }

    /**
     * Overwrite signal handlers with the defined handler.
     *
     * @see    handle()
     *
     * @param string|int   $signal  Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|int $handler The signal handler.
     */
    public function setHandler($signal, $handler)
    {
        $this->handle($signal, $handler, 'set');
    }

    /**
     * Appends the handler to the current signal handler stack.
     *
     * @see    handle()
     *
     * @param string|int   $signal  Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|int $handler The signal handler
     */
    public function appendHandler($signal, $handler)
    {
        $this->handle($signal, $handler, 'append');
    }

    /**
     * Prepends the handler to the current signal handler stack.
     *
     * @see    handle()
     *
     * @param string|int   $signal  Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|int $handler The signal handler
     */
    public function prependHandler($signal, $handler)
    {
        $this->handle($signal, $handler, 'prepend');
    }

    /**
     * Send a signal to a process.
     *
     * @param int|string $signal    Signal (code or name) to send.
     * @param int        $processId Process id to send signal, if not defined will use the current one.
     *
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
     *
     * @param string|int $signal Signal name, PCNTL constant name or PCNTL constant value.
     *
     * @throws InvalidArgumentException Then signal is not a valid signal.
     *
     * @return int
     */
    protected function translateSignal($signal)
    {
        if (isset($this->signals[$signal])) {
            $signal = $this->signals[$signal];
        } elseif (defined($signal)) {
            $signal = constant($signal);
        }

        if (! is_int($signal)) {
            throw new InvalidArgumentException('The given value is not a valid signal');
        }

        return $signal;
    }

    /**
     * Handles the signals using all handlers in the stack.
     *
     * @param int $signalNumber Signal number to be handled.
     */
    public function __invoke($signalNumber)
    {
        foreach ($this->getHandlers($signalNumber) as $handler) {
            call_user_func($handler, $signalNumber);
        }
    }
}
