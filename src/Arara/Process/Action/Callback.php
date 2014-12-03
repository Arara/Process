<?php

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;
use InvalidArgumentException;

/**
 * {@inheritDoc}
 */
class Callback implements Action
{
    protected $callback;
    protected $handlers = array();

    /**
     * @param callable $callback Callback to run as action.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
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
            call_user_func($handler, $control, $context);
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
        $this->handlers[$event] = $handler;
    }

    /**
     * Returns all defined handlers.
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }
}
