<?php

namespace Arara\Process;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Pidfile
 */
class PidfileTest extends TestCase
{
    protected function init()
    {
        $this->overwrite(
            'fopen',
            function () {
                return 'a resource';
            }
        );

        $this->overwrite(
            'fgets',
            function () {
                return '';
            }
        );

        $this->overwrite(
            'is_dir',
            function () {
                return true;
            }
        );

        $this->overwrite(
            'is_writable',
            function () {
                return true;
            }
        );

        $this->overwrite(
            'flock',
            function () {
                return true;
            }
        );

        $this->overwrite(
            'fseek',
            function () {
                return 0;
            }
        );

        $this->overwrite(
            'ftruncate',
            function () {
                return true;
            }
        );

        $this->overwrite(
            'fwrite',
            function ($content) {
                return strlen($content);
            }
        );

        $this->overwrite(
            'fclose',
            function () {
                return true;
            }
        );

        $this->overwrite(
            'unlink',
            function () {
                return true;
            }
        );
    }

    public function testShouldAcceptAControlOnConstructor()
    {
        $control = new Control();
        $pidfile = new Pidfile($control);

        $this->assertAttributeSame($control, 'control', $pidfile);
    }

    public function testShouldHaveAnApplicationNameByDefault()
    {
        $control = new Control();
        $pidfile = new Pidfile($control);

        $this->assertSame('arara', $pidfile->getApplicationName());
    }

    public function testShouldDefineAnApplicationNameOnConstructor()
    {
        $control = new Control();
        $applicationName = 'application42';
        $pidfile = new Pidfile($control, $applicationName);

        $this->assertSame('application42', $pidfile->getApplicationName());
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage Application name should be lowercase
     */
    public function testShouldThrowsAnExceptionWhenApplicationNameIsNotALowerCaseString()
    {
        $control = new Control();
        $applicationName = 'Application';

        new Pidfile($control, $applicationName);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage Application name should contains only alphanumeric chars
     */
    public function testShouldThrowsAnExceptionWhenApplicationNameIsContainsNonAlphanumericChars()
    {
        $control = new Control();
        $applicationName = 'application-name';

        new Pidfile($control, $applicationName);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage Application name should be no longer than 16 characters
     */
    public function testShouldThrowsAnExceptionWhenApplicationNameHasLen()
    {
        $control = new Control();
        $applicationName = 'thisisthebigestapplicationnameoftheuniverse';

        new Pidfile($control, $applicationName);
    }

    public function testShouldHaveALockDirectoryByDefault()
    {
        $control = new Control();
        $pidfile = new Pidfile($control);

        $this->assertAttributeSame('/var/run', 'lockDirectory', $pidfile);
    }

    public function testShouldDefineALockDirectoryOnConstructor()
    {
        $control = new Control();
        $lockDirectory = '/run/lock';
        $pidfile = new Pidfile($control, 'arara', $lockDirectory);

        $this->assertAttributeSame($lockDirectory, 'lockDirectory', $pidfile);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage "filename" is not a valid directory
     */
    public function testShouldThrowsAnExceptionWhenLockDirectoryIsNotADirectory()
    {
        $this
            ->restore('is_dir')
            ->overwrite(
                'is_dir',
                function () {
                    return false;
                }
            );

        $control = new Control();
        new Pidfile($control, 'arara', 'filename');
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage "filename" is not a writable directory
     */
    public function testShouldThrowsAnExceptionWhenLockDirectoryIsNotWritable()
    {
        $this
            ->restore('is_writable')
            ->overwrite(
                'is_writable',
                function () {
                    return false;
                }
            );

        $control = new Control();
        new Pidfile($control, 'arara', 'filename');
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not open pidfile
     */
    public function testShouldThrowsAnExceptionIfCanNotOpenPidfileWhenCheckingIfIsActive()
    {
        $this
            ->restore('fopen')
            ->overwrite(
                'fopen',
                function () {
                    return false;
                }
            );


        $control = new Control();
        $pidfile = new Pidfile($control, 'arara', 'filename');
        $pidfile->isActive();
    }

    public function testShouldNotReturnAsActiveWhenPidfileIsEmpty()
    {
        $control = new Control();
        $pidfile = new Pidfile($control);

        $this->assertFalse($pidfile->isActive());
    }

    public function testShouldCheckIfPidIsActiveWhenPidfileContainsProcessId()
    {
        $processId = 123456;

        $this
            ->restore('fgets')
            ->overwrite(
            'fgets',
                function () use ($processId) {
                    return $processId . PHP_EOL;
                }
            );


        $signal = $this->getMock('Arara\Process\Control\Signal');
        $signal
            ->expects($this->once())
            ->method('send')
            ->with(0, $processId)
            ->will($this->returnValue(true));

        $control = $this->getMock('Arara\Process\Control');
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($signal));

        $pidfile = new Pidfile($control);

        $this->assertTrue($pidfile->isActive());
    }

    public function testShouldReturnPidWhenHasPidOnPidfile()
    {
        $processId = 123456;
        $this
            ->restore('fgets')
            ->overwrite(
                'fgets',
                function () use ($processId) {
                    return $processId;
                }
            );

        $control = $this->getMock('Arara\Process\Control');

        $pidfile = new Pidfile($control);

        $this->assertEquals($processId, $pidfile->getProcessId());
    }

    public function testShouldReturnNullWhenThereIsNoPidOnPidfile()
    {
        $control = $this->getMock('Arara\Process\Control');

        $pidfile = new Pidfile($control);

        $this->assertNull($pidfile->getProcessId());
    }

    public function testShouldReturnOnlyTheFirstLineOfThePidWhenPidfileIsNotEmpty()
    {
        $processId = 123456;
        $this
            ->restore('fgets')
            ->overwrite(
                'fgets',
                function () use ($processId) {
                    return $processId . PHP_EOL . 987981723 . 12387687;
                }
            );

        $control = $this->getMock('Arara\Process\Control');

        $pidfile = new Pidfile($control);

        $this->assertEquals($processId, $pidfile->getProcessId());
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Process is already active
     */
    public function testShouldThrowsAnExceptionIfIsIsAlreadyActiveWhenInitializing()
    {
        $this
            ->restore('fgets')
            ->overwrite(
                'fgets',
                function () {
                    return 123456;
                }
            );

        $signal = $this->getMock('Arara\Process\Control\Signal');
        $signal
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue(true));

        $control = $this->getMock('Arara\Process\Control');
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($signal));

        $pidfile = new Pidfile($control);
        $pidfile->initialize();
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not lock pidfile
     */
    public function testShouldThrowsAnExceptionIfCouldNotLockPidfileWhenInitializing()
    {
        $this
            ->restore('flock')
            ->overwrite(
                'flock',
                function () {
                    return false;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not seek pidfile cursor
     */
    public function testShouldThrowsAnExceptionIfCouldNotSeekPidfileWhenInitializing()
    {
        $this
            ->restore('fseek')
            ->overwrite(
                'fseek',
                function () {
                    return -1;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not truncate pidfile
     */
    public function testShouldThrowsAnExceptionIfCouldNotTruncatePidfileWhenInitializing()
    {
        $this
            ->restore('ftruncate')
            ->overwrite(
                'ftruncate',
                function () {
                    return false;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not write on pidfile
     */
    public function testShouldThrowsAnExceptionIfCouldNotWritePidfileWhenInitializing()
    {
        $this
            ->restore('fwrite')
            ->overwrite(
                'fwrite',
                function () {
                    return false;
                }
            );


        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    public function testShouldWriteProcessIdOnPidfileWhenInitializing()
    {
        $processId = 123456;
        $actualContent = null;
        $expectedContent = $processId . PHP_EOL;

        $this
            ->restore('fwrite')
            ->overwrite(
                'fwrite',
                function () use (&$actualContent) {
                    $actualContent = func_get_arg(1);

                    return true;
                }
            );


        $signal = $this->getMock('Arara\Process\Control\Signal');
        $signal
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue(false));

        $info = $this->getMock('Arara\Process\Control\Info');
        $info
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($processId));

        $control = $this->getMock('Arara\Process\Control');
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($signal));
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($info));

        $pidfile = new Pidfile($control);
        $pidfile->initialize();

        $this->assertSame($expectedContent, $actualContent);
    }

    public function testShouldUnlockPidfileWhenFinilizing()
    {
        $actualArgument = null;
        $expectedArgument = LOCK_UN;
        $this
            ->restore('flock')
            ->overwrite(
                'flock',
                function () use (&$actualArgument) {
                    $actualArgument = func_get_arg(1);

                    return true;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->finalize();

        $this->assertSame($expectedArgument, $actualArgument);
    }

    public function testShouldClosePidfileWhenFinilizing()
    {
        $actualCount = 0;
        $expectedCount = 1;
        $this
            ->restore('fclose')
            ->overwrite(
                'fclose',
                function () use (&$actualCount) {
                    $actualCount++;

                    return true;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->finalize();

        $this->assertSame($expectedCount, $actualCount);
    }

    public function testShouldRemovePidfileWhenFinilizing()
    {
        $actualPidfile = null;
        $expectedPidfile = '/var/run/arara.pid';
        $this
            ->restore('unlink')
            ->overwrite(
                'unlink',
                function () use (&$actualPidfile) {
                    $actualPidfile = func_get_arg(0);

                    return true;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->finalize();

        $this->assertEquals($expectedPidfile, $actualPidfile);
    }

    public function testShouldReadFileContentOnce()
    {
        $actualCount = 0;
        $expectedCount = 1;
        $this
            ->restore('fgets')
            ->overwrite(
                'fgets',
                function () use (&$actualCount) {
                    $actualCount++;

                    return 123456;
                }
            );

        $pidfile = new Pidfile(new Control());
        $pidfile->getProcessId();
        $pidfile->getProcessId();
        $pidfile->getProcessId();

        $this->assertEquals($expectedCount, $actualCount);
    }
}
