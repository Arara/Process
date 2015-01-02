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
    protected $signals = array(
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
    );

    /**
     * May contain signals handlers.
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * Define the time (in seconds) to send an alarm to the current process.
     *
     * @param integer $seconds Time (in seconds) to send an alarm.
     *
     * @return integer
     */
    public function alarm($seconds)
    {
        return pcntl_alarm($seconds);
    }

    /**
     * Calls signal handlers for pending signals.
     *
     *
     * @return boolean
     */
    public function dispatch()
    {
        return pcntl_signal_dispatch();
    }

    /**
     * Register a signal handler
     *
     * @throws RuntimeException When can not register handler.
     *
     * @param integer          $signalNumber Signal number.
     * @param callable|integer $handler      The signal handler.
     *
     * @return null
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
     * @param string|integer   $signal    Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|integer $handler   The signal handler.
     * @param string           $placement Placement of handler ("set", "append" or "prepend").
     *
     * @return null
     */
    protected function handle($signal, $handler, $placement)
    {
        declare (ticks = 1);

        $signalNumber = $this->translateSignal($signal);

        if (is_int($handler) && in_array($handler, array(SIG_IGN, SIG_DFL))) {
            unset($this->handlers[$signalNumber]);
            $this->registerHandler($signalNumber, $handler);

            return;
        }

        $this->placeHandler($signalNumber, $handler, $placement);
    }

    /**
     * Define a callback handler for the given signal.
     *
     * @param integer  $signalNumber Signal number.
     * @param callable $handler      The signal handler.
     * @param string   $placement    Placement of handler ("set", "append" or "prepend").
     *
     * @return null
     */
    protected function placeHandler($signalNumber, callable $handler, $placement)
    {
        if (! isset($this->handlers[$signalNumber])) {
            $this->handlers[$signalNumber] = array();
            $this->registerHandler($signalNumber, $this);
        }

        switch ($placement) {
            case 'set':
                $this->handlers[$signalNumber] = array($handler);
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
     * @param integer|string $signal
     *
     * @return array
     */
    public function getHandlers($signal)
    {
        $signalNumber = $this->translateSignal($signal);
        $handlers = array();
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
     * @param string|integer   $signal  Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|integer $handler The signal handler.
     *
     * @return null
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
     * @param string|integer   $signal  Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|integer $handler The signal handler
     *
     * @return null
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
     * @param string|integer   $signal  Signal name, PCNTL constant name or PCNTL constant value.
     * @param callable|integer $handler The signal handler
     *
     * @return null
     */
    public function prependHandler($signal, $handler)
    {
        $this->handle($signal, $handler, 'prepend');
    }

    /**
     * Send a signal to a process.
     *
     * @param integer|string $signal    Signal (code or name) to send.
     * @param integer        $processId Process id to send signal, if not defined will use the current one.
     *
     * @return boolean
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
     * @throws InvalidArgumentException Then signal is not a valid signal.
     *
     * @param string|integer $signal Signal name, PCNTL constant name or PCNTL constant value.
     *
     * @return integer
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
     * @param integer $signalNumber Signal number to be handled.
     *
     * @return null
     */
    public function __invoke($signalNumber)
    {
        foreach ($this->getHandlers($signalNumber) as $handler) {
            call_user_func($handler, $signalNumber);
        }
    }
}
