<?php

namespace Arara\Process\Action;

use Arara\Process\Control;

/**
 * @covers Arara\Process\Action\Callback
 */
class CallbackTest extends \TestCase
{
    public function testShouldDefineACallbackActionOnConstructor()
    {
        $action = function () {};
        $callback = new Callback($action);

        $this->assertAttributeSame($action, 'callback', $callback);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Given action is not a valid callback
     */
    public function testShouldThrowsAnExceptionWhenActionIsNotAValidCallback()
    {
        $action = array();
        $callback = new Callback($action);
    }

    public function testShouldRunDefinedCallback()
    {
        $control = new Control();
        $counter = 0;
        $action = function () use (&$counter) {
            $counter++;
        };
        $callback = new Callback($action);
        $callback->execute($control);

        $this->assertEquals(1, $counter);
    }

    public function testShouldAddEventHandlers()
    {
        $event = Callback::EVENT_SUCCESS;
        $handler = function() {};
        $expectedHandlers = array($event => $handler);

        $callback = new Callback(function() {});
        $callback->bind($event, $handler);

        $this->assertAttributeSame($expectedHandlers, 'handlers', $callback);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Given event handler is not a valid callback
     */
    public function testShouldThrowsAnExceptionWhenEventHandlerIsNotAValidCallback()
    {
        $callback = new Callback(function() {});
        $callback->bind(Callback::EVENT_START, array());
    }

    public function testShouldRunDefinedEventHandler()
    {
        $control = new Control();
        $event = Callback::EVENT_SUCCESS;
        $counter = 0;
        $handler = function () use (&$counter) {
            $counter++;
        };

        $callback = new Callback(function() {});
        $callback->bind($event, $handler);
        $callback->trigger($event, $control, array());

        $this->assertEquals(1, $counter);
    }

    public function testShouldRunDefinedEventHandlerEvenWhenMultipleEventsAreDescribed()
    {
        $control = new Control();
        $counter = 0;
        $handler = function () use (&$counter) {
            $counter++;
        };

        $callback = new Callback(function() {});
        $callback->bind(Callback::EVENT_ERROR | Callback::EVENT_FAILURE, $handler);
        $callback->trigger(Callback::EVENT_ERROR, $control, array());
        $callback->trigger(Callback::EVENT_FAILURE, $control, array());

        $this->assertEquals(2, $counter);
    }

    public function testShouldNotRunEventHandlerWhenEventIsNotTriggered()
    {
        $control = new Control();
        $counter = 0;
        $handler = function () use (&$counter) {
            $counter++;
        };

        $callback = new Callback(function() {});
        $callback->bind(Callback::EVENT_ERROR, $handler);
        $callback->trigger(Callback::EVENT_FAILURE, $control, array());

        $this->assertEquals(0, $counter);
    }
}
