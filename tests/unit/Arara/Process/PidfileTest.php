<?php

namespace Arara\Process;

function fgets()
{
    $return = null;
    $GLOBALS['arara']['fgets']['args'] = func_get_args();
    if (isset($GLOBALS['arara']['fgets']['return'])) {
        $return = $GLOBALS['arara']['fgets']['return'];
        unset($GLOBALS['arara']['fgets']['return']);
    } else {
        $return = '';
    }

    return $return;
}

function flock()
{
    $return = null;
    $GLOBALS['arara']['flock']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['flock'])) {
        $return = $GLOBALS['arara']['flock']['return'];
    } else {
        $return = true;
    }

    return $return;
}

function fopen()
{
    $return = null;
    $GLOBALS['arara']['fopen']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['fopen'])) {
        $return = $GLOBALS['arara']['fopen']['return'];
    } else {
        $return = true;
    }

    return $return;
}

function fclose()
{
    $return = null;
    $GLOBALS['arara']['fclose']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['fclose'])) {
        $return = $GLOBALS['arara']['fclose']['return'];
    }

    return $return;
}

function fwrite()
{
    $return = null;
    $GLOBALS['arara']['fwrite']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['fwrite'])) {
        $return = $GLOBALS['arara']['fwrite']['return'];
    } else {
        $return = true;
    }

    return $return;
}

function is_dir($directory)
{
    $return = null;
    $GLOBALS['arara']['is_dir']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['is_dir'])) {
        $return = $GLOBALS['arara']['is_dir']['return'];
    } else {
        $return = true;
    }

    return $return;
}

function is_writable($filename)
{
    $return = null;
    $GLOBALS['arara']['is_writable']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['is_writable'])) {
        $return = $GLOBALS['arara']['is_writable']['return'];
    } else {
        $return = true;
    }

    return $return;
}

function unlink()
{
    $return = null;
    $GLOBALS['arara']['unlink']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['unlink'])) {
        $return = $GLOBALS['arara']['unlink']['return'];
    }

    return $return;
}

function fseek()
{
    $return = null;
    $GLOBALS['arara']['fseek']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['fseek'])) {
        $return = $GLOBALS['arara']['fseek']['return'];
    } else {
        $return = 0;
    }

    return $return;
}

function ftruncate()
{
    $return = null;
    $GLOBALS['arara']['ftruncate']['args'] = func_get_args();
    if (array_key_exists('return', $GLOBALS['arara']['ftruncate'])) {
        $return = $GLOBALS['arara']['ftruncate']['return'];
    } else {
        $return = true;
    }

    return $return;
}

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Pidfile
 */
class PidfileTest extends TestCase
{
    protected function init()
    {
        $GLOBALS['arara']['fopen']['return'] = 'a non-false value';
        $GLOBALS['arara']['is_dir']['return'] = true;
        $GLOBALS['arara']['is_writable']['return'] = true;
        $GLOBALS['arara']['flock']['return'] = true;
        $GLOBALS['arara']['fseek']['return'] = 0;
        $GLOBALS['arara']['ftruncate']['return'] = true;
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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Application name should be lowercase
     */
    public function testShouldThrowsAnExceptionWhenApplicationNameIsNotALowerCaseString()
    {
        $control = new Control();
        $applicationName = 'Application';

        new Pidfile($control, $applicationName);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Application name should contains only alphanumeric chars
     */
    public function testShouldThrowsAnExceptionWhenApplicationNameIsContainsNonAlphanumericChars()
    {
        $control = new Control();
        $applicationName = 'application-name';

        new Pidfile($control, $applicationName);
    }

    /**
     * @expectedException InvalidArgumentException
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
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage "filename" is not a valid directory
     */
    public function testShouldThrowsAnExceptionWhenLockDirectoryIsNotADirectory()
    {
        $GLOBALS['arara']['is_dir']['return'] = false;
        $control = new Control();
        new Pidfile($control, 'arara', 'filename');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage "filename" is not a writable directory
     */
    public function testShouldThrowsAnExceptionWhenLockDirectoryIsNotWritable()
    {
        $GLOBALS['arara']['is_writable']['return'] = false;
        $control = new Control();
        new Pidfile($control, 'arara', 'filename');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not open pidfile
     */
    public function testShouldThrowsAnExceptionIfCanNotOpenPidfileWhenCheckingIfIsActive()
    {
        $GLOBALS['arara']['fopen']['return'] = false;

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
        $GLOBALS['arara']['fgets']['return'] = $processId . PHP_EOL;

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
        $GLOBALS['arara']['fgets']['return'] = $processId;

        $control = $this->getMock('Arara\Process\Control');

        $pidfile = new Pidfile($control);

        $this->assertEquals($processId, $pidfile->getProcessId());
    }

    public function testShouldReturnNullWhenThereIsNoPidOnPidfile()
    {
        $GLOBALS['arara']['fgets']['return'] = '';

        $control = $this->getMock('Arara\Process\Control');

        $pidfile = new Pidfile($control);

        $this->assertNull($pidfile->getProcessId());
    }

    public function testShouldReturnOnlyTheFirstLineOfThePidWhenPidfileIsNotEmpty()
    {
        $processId = 123456;
        $GLOBALS['arara']['fgets']['return'] = $processId . PHP_EOL . 987981723 . 12387687;

        $control = $this->getMock('Arara\Process\Control');

        $pidfile = new Pidfile($control);

        $this->assertEquals($processId, $pidfile->getProcessId());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Process is already active
     */
    public function testShouldThrowsAnExceptionIfIsIsAlreadyActiveWhenInitializing()
    {
        $GLOBALS['arara']['fgets']['return'] = 123456;

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
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not lock pidfile
     */
    public function testShouldThrowsAnExceptionIfCouldNotLockPidfileWhenInitializing()
    {
        $GLOBALS['arara']['flock']['return'] = false;

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not seek pidfile cursor
     */
    public function testShouldThrowsAnExceptionIfCouldNotSeekPidfileWhenInitializing()
    {
        $GLOBALS['arara']['fseek']['return'] = -1;

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not truncate pidfile
     */
    public function testShouldThrowsAnExceptionIfCouldNotTruncatePidfileWhenInitializing()
    {
        $GLOBALS['arara']['ftruncate']['return'] = false;

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not write on pidfile
     */
    public function testShouldThrowsAnExceptionIfCouldNotWritePidfileWhenInitializing()
    {
        $GLOBALS['arara']['fwrite']['return'] = false;

        $pidfile = new Pidfile(new Control());
        $pidfile->initialize();
    }

    public function testShouldWriteProcessIdOnPidfileWhenInitializing()
    {
        $processId = 123456;
        $GLOBALS['arara']['flock']['return'] = 'non-false';
        $GLOBALS['arara']['fwrite']['return'] = 'nono-false';

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

        $this->assertEquals($processId . PHP_EOL, $GLOBALS['arara']['fwrite']['args'][1]);
    }

    public function testShouldUnlockPidfileWhenFinilizing()
    {
        $pidfile = new Pidfile(new Control());
        $pidfile->finalize();

        $this->assertEquals(LOCK_UN, $GLOBALS['arara']['flock']['args'][1]);
    }

    public function testShouldClosePidfileWhenFinilizing()
    {
        $pidfile = new Pidfile(new Control());
        $pidfile->finalize();

        $this->assertArrayHasKey('fclose', $GLOBALS['arara']);
    }

    public function testShouldRemovePidfileWhenFinilizing()
    {
        $pidfile = new Pidfile(new Control());
        $pidfile->finalize();

        $this->assertEquals('/var/run/arara.pid', $GLOBALS['arara']['unlink']['args'][0]);
    }

    public function testShouldReadFileContentOnce()
    {
        $GLOBALS['arara']['fgets']['return'] = 123456;

        $pidfile = new Pidfile(new Control());

        $this->assertEquals(123456, $pidfile->getProcessId());
        $this->assertEquals(123456, $pidfile->getProcessId());
    }
}
