<?php

use Arara\Process\Action\Action;
use Arara\Process\Context;
use Arara\Process\Control;

class ActionClass implements Action
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function execute(Control $control, Context $context)
    {
        echo sprintf('[%d] Action "%s" execution', $control->info()->getId(), $this->id) . PHP_EOL;
        sleep(1);
    }

    public function trigger($event, Control $control, Context $context)
    {
        $eventName = null;
        switch ($event) {
            case self::EVENT_INIT:
                $eventName = 'EVENT_INIT';
                break;
            case self::EVENT_FORK:
                $eventName = 'EVENT_FORK';
                break;
            case self::EVENT_START:
                $eventName = 'EVENT_START';
                break;
            case self::EVENT_SUCCESS:
                $eventName = 'EVENT_SUCCESS';
                break;
            case self::EVENT_ERROR:
                $eventName = 'EVENT_ERROR';
                break;
            case self::EVENT_FAILURE:
                $eventName = 'EVENT_FAILURE';
                break;
            case self::EVENT_TIMEOUT:
                $eventName = 'EVENT_TIMEOUT';
                break;
            case self::EVENT_FINISH:
                $eventName = 'EVENT_FINISH';
                break;
            default:
                $eventName = 'Unrecognized';
        }
        echo sprintf('[%d] Action "%s" on "%s"', $control->info()->getId(), $this->id, $eventName) . PHP_EOL;
    }
}
