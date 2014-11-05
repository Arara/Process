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

    public function execute(Control $control)
    {
        echo sprintf('[%d] Action "%s" execution', $control->info()->getId(), $this->id) . PHP_EOL;
        sleep(1);
    }

    public function trigger($event, Control $control, Context $context)
    {
        $eventName = null;
        switch ($event) {
            case self::EVENT_START:
                $eventName = 'Start';
                break;
            case self::EVENT_SUCCESS:
                $eventName = 'Success';
                break;
            case self::EVENT_ERROR:
                $eventName = 'Error';
                break;
            case self::EVENT_FAILURE:
                $eventName = 'Failure';
                break;
            case self::EVENT_TIMEOUT:
                $eventName = 'Timeout';
                break;
            case self::EVENT_FINISH:
                $eventName = 'Finish';
                break;
            default:
                $eventName = 'Unrecognized';
        }
        echo sprintf('[%d] Action "%s" on "%s"', $control->info()->getId(), $this->id, $eventName) . PHP_EOL;
    }
}
