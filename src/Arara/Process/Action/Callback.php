<?php

namespace Arara\Process\Action;

use Arara\Process\Control;
use InvalidArgumentException;

/**
 * {@inheritDoc}
 */
class Callback implements Action
{
    private $callback;
    private $handlers = array();

    /**
     * @param callable $callback Callback to run as action.
     */
    public function __construct($callback)
    {
        if (! is_callable($callback)) {
            throw new InvalidArgumentException('Given action is not a valid callback');
        }

        $this->callback = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Control $control)
    {
        return call_user_func($this->callback, $control);
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($event, Control $control, array $context)
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
    public function bind($event, $handler)
    {
        if (! is_callable($handler)) {
            throw new InvalidArgumentException('Given event handler is not a valid callback');
        }

        $this->handlers[$event] = $handler;
    }
}
