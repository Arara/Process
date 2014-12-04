<?php

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;
use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Action\Command
 */
class CommandTest extends TestCase
{
    protected $defaultExecCallback;

    protected function init()
    {
        $this->defaultExecCallback = function ($command, &$output, &$return_var) {
            $output = array('first', 'Second', 'Third');
            $return_var = 0;

            return end($output);
        };
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
        $this->overwrite('exec', $this->defaultExecCallback);

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
        $this->overwrite('exec', $this->defaultExecCallback);

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
        $this->overwrite('exec', $this->defaultExecCallback);

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
        $actualCommand = null;
        $expectedCommand = "(echo 'Arara\Process')2>&1";

        $this->overwrite(
            'exec',
            function ($command, &$output, &$return_var) use (&$actualCommand) {
                $output = array();
                $return_var = 0;
                $actualCommand = $command;

                return '';
            }
        );

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\Process'), false);
        $action->execute($control, $context);

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     */
    public function testShouldThowsAnExceptionWhenCommandGetError()
    {
        $this->overwrite(
            'exec',
            function ($command, &$output, &$return_var) {
                $output = array();
                $return_var = 1;

                return '';
            }
        );

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'), false);
        $action->execute($control, $context);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Second
     */
    public function testShouldThowsAnExceptionWithLastLineAsMessageWhenCommandGetError()
    {
        $this->overwrite(
            'exec',
            function ($command, &$output, &$return_var) {
                $output = array('First', 'Second');
                $return_var = 1;

                return end($output);
            }
        );

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'), false);
        $action->execute($control, $context);
    }

    public function testShouldStoreLastOutputLine()
    {
        $expectedTail = 'Process';

        $this->overwrite(
            'exec',
            function ($command, &$output, &$return_var) use ($expectedTail) {
                $output = array('Arara', $expectedTail);
                $return_var = 0;

                return end($output);
            }
        );

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualTail = $context->outputTail;

        $this->assertEquals($expectedTail, $actualTail);
    }

    public function testShouldStoreOutputLines()
    {
        $expectedLines = array('Arara', 'Process');

        $this->overwrite(
            'exec',
            function ($command, &$output, &$return_var) use ($expectedLines) {
                $output = $expectedLines;
                $return_var = 0;

                return end($output);
            }
        );

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualLines = $context->outputLines;

        $this->assertEquals($expectedLines, $actualLines);
    }

    public function testShouldStoreOutputString()
    {
        $expectedString = 'Arara' . PHP_EOL . 'Process';

        $this->overwrite(
            'exec',
            function ($command, &$output, &$return_var) use ($expectedString) {
                $output = explode(PHP_EOL, $expectedString);
                $return_var = 0;

                return end($output);
            }
        );

        $control = new Control();
        $context = new Context();
        $action = new Command('echo', array('Arara\nProcess'));
        $action->execute($control, $context);

        $actualString = $context->outputString;

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
