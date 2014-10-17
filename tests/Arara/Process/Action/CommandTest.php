<?php

namespace Arara\Process\Action;

function exec($command, &$output = null, &$return_var = null)
{
    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['exec']['args'] = $args;

    if (array_key_exists('output', $GLOBALS['arara']['exec'])) {
        $output = $GLOBALS['arara']['exec']['output'];
        $return = end($output);
    } else {
        \exec($command, $output, $return_var);
    }

    if (isset($GLOBALS['arara']['exec']['return_var'])) {
        $return_var = $GLOBALS['arara']['exec']['return_var'];
    }

    return $return;
}

use Arara\Process\Context;
use Arara\Process\Control;

/**
 * @covers Arara\Process\Action\Command
 */
class CommandTest extends \TestCase
{
    protected function init()
    {
        $GLOBALS['arara']['exec']['return'] = null;
        $GLOBALS['arara']['exec']['output'] = array('');
        $GLOBALS['arara']['exec']['return_var'] = 0;
    }

    protected function finish()
    {
        unset($GLOBALS['arara']['exec']);
    }

    public function testShouldAcceptCommandAndPrefixItOnConstructor()
    {
        $action = new Command('whoami');

        $actualCommand = $action->getCommand();
        $expectedCommand = '/usr/bin/env whoami';

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    public function testShouldAcceptArgumentsOnConstructor()
    {
        $arguments = array(__DIR__, '-name' => '*');
        $action = new Command('find', $arguments);

        $this->assertEquals($arguments, $action->getArguments());
    }

    public function testShouldRemoveCommandPrefixOnConstructor()
    {
        $action = new Command('echo', array(), false);

        $actualCommand = $action->getCommand();
        $expectedCommand = 'echo';

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    public function testShouldAssembleCommandAndArguments()
    {
        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara', 'Process'));
        $action->execute($control, $context);

        $actualCommand = $context->command;
        $expectedCommand = "/usr/bin/env echo 'Arara' 'Process'";

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    public function testShouldAssembleCommandAndKeyValueArguments()
    {
        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('-n' => 'Process'));
        $action->execute($control, $context);

        $actualCommand = $context->command;
        $expectedCommand = "/usr/bin/env echo '-n' 'Process'";

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    public function testShouldNotAssembleWithEnvCommand()
    {
        $control = new Control();
        $context = new Context();
        $prefixEnv = false;
        $action = new Command('echo', array('Arara\Process'), $prefixEnv);
        $action->execute($control, $context);

        $actualCommand = $context->command;
        $expectedCommand = "echo 'Arara\Process'";

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    public function testShouldExecuteCommandAndRedirectStderr()
    {
        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\Process'), false);
        $action->execute($control, $context);

        $actualCommand = $GLOBALS['arara']['exec']['args'][0];
        $expectedCommand = "(echo 'Arara\Process')2>&1";

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage An error has occurred
     */
    public function testShouldThowsAnExceptionWhenCommandGetError()
    {
        $GLOBALS['arara']['exec']['output'] = array('echo', 'An error has occurred');
        $GLOBALS['arara']['exec']['return_var'] = 1;

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'), false);
        $action->execute($control, $context);
    }

    public function testShouldStoreLastOutputLine()
    {
        $GLOBALS['arara']['exec']['output'] = array('Arara', 'Process');

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualTail = $context->outputTail;
        $expectedTail = 'Process';

        $this->assertEquals($expectedTail, $actualTail);
    }

    public function testShouldStoreOutputLines()
    {
        $GLOBALS['arara']['exec']['output'] = array('Arara', 'Process');

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualLines = $context->outputLines;
        $expectedLines = $GLOBALS['arara']['exec']['output'];

        $this->assertEquals($expectedLines, $actualLines);
    }

    public function testShouldStoreOutputString()
    {
        $GLOBALS['arara']['exec']['output'] = array('Arara', 'Process');

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualString = $context->outputString;
        $expectedString = 'Arara' . PHP_EOL . 'Process';

        $this->assertEquals($expectedString, $actualString);
    }

    public function testShouldStoreReturnValue()
    {
        $GLOBALS['arara']['exec']['return_var'] = 0;

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualReturn = $context->returnValue;
        $expectedReturn = 0;

        $this->assertEquals($expectedReturn, $actualReturn);
    }
}
