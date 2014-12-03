<?php

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;
use PHPFluent\Callback\Callback as FluentCallback;

/**
 * {@inheritDoc}
 */
class Callback implements Action
{
    /**
     * @var FluentCallback
     */
    protected $callback;

    /**
     * @var array
     */
    protected $handlers = array();

    /**
     * @param callable $callback Callback to run as action.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $this->fluentCallback($callback);
    }

    /**
     * @return callable
     */
    public function getCallable()
    {
        return $this->callback->getCallable();
    }

    /**
     * Creates a fluent callback based by the given callable.
     *
     * @return FluentCallback
     */
    protected function fluentCallback(callable $callable)
    {
        return new FluentCallback($callable);
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Control $control, Context $context)
    {
        return call_user_func($this->callback, $control, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($event, Control $control, Context $context)
    {
        foreach ($this->handlers as $key => $handler) {
            if ($event !== ($key & $event)) {
                continue;
            }
            call_user_func($handler, $event, $control, $context);
            break;
        }
    }

    /**
     * Bind a handler for event/events.
     *
     * @param  int      $event   Event to handle
     * @param  callable $handler Callback to handle the event (or events).
     * @return void
     */
    public function bind($event, callable $handler)
    {
        $this->handlers[$event] = $this->fluentCallback($handler);
    }

    /**
     * Returns all defined handlers.
     *
     * @return array
     */
    public function getHandlers()
    {
        $handlers = array();
        foreach ($this->handlers as $key => $handler) {
            $handlers[$key] = $handler->getCallable();
        }

        return $handlers;
    }
}
