<?php

namespace Arara\Test;

use Arara\Process\Action\Action;
use Arara\Process\Context;
use Arara\Process\Control;

/**
 * Action for tests purposes.
 */
class TestAction implements Action
{
    const ACTION = 2;
    const EVENTS = 4;

    /**
     * Action ID.
     *
     * @var int
     */
    protected $actionId;

    /**
     * Output flags.
     *
     * @var int
     */
    protected $output;

    /**
     * Events list.
     *
     * @var array
     */
    protected $events = array (
        self::EVENT_INIT    => 'Init',
        self::EVENT_FORK    => 'Fork',
        self::EVENT_START   => 'Start',
        self::EVENT_SUCCESS => 'Success',
        self::EVENT_ERROR   => 'Error',
        self::EVENT_FAILURE => 'Failure',
        self::EVENT_TIMEOUT => 'Timeout',
        self::EVENT_FINISH  => 'Finish',
    );

    /**
     * @var array
     */
    public $records = array();

    /**
     * @param  int $actionId
     * @param  int $output
     */
    public function __construct($actionId, $output = self::ACTION)
    {
        $this->actionId = $actionId;
        $this->output   = $output;
    }

    /**
     * Creates a log (output).
     *
     * @param  string $event
     * @param  Control $control
     * @param  boolean $output
     * @return void
     */
    protected function log($event, Control $control, $output)
    {
        $record = array(
            'date' => date('Y-m-d H:i:s'),
            'pid' => $control->info()->getId(),
            'actionId' => $this->actionId,
            'event' => $event,
        );
        $this->records[] = $record;

        if (! $output) {
            return;
        }

        $message = sprintf(
            '[%s] PID: %d, Id: %d, Event: %s',
            $record['date'],
            $record['pid'],
            $record['actionId'],
            $record['event']
        );

        echo $message . PHP_EOL;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Control $control, Context $context)
    {
        $this->log('Execution', $control, (self::ACTION === ($this->output & self::ACTION)));
        usleep(500000);
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($event, Control $control, Context $context)
    {
        $this->log($this->events[$event], $control, (self::EVENTS === ($this->output & self::EVENTS)));
    }
}
