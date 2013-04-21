<?php

namespace Arara\Process;

function posix_getuid() { return $GLOBALS['posix_getuid']; }
function posix_getgid() { return $GLOBALS['posix_getgid']; }
function posix_getpwuid() { return $GLOBALS['posix_getpwuid']; }
function posix_getgrgid() { return $GLOBALS['posix_getgrgid']; }
function pcntl_fork() { return $GLOBALS['pcntl_fork']; }

class ArrayIpc implements Ipc\Ipc
{
    private $data = array();
    public function save($name, $value) { $this->data[$name] = $value; }
    public function load($name) { if (isset($this->data[$name])) { return $this->data[$name]; } }
    public function destroy() { $this->data = array(); }
}


class ItemTest extends \PHPUnit_Framework_TestCase
{


    protected function setUp()
    {
        $GLOBALS['posix_getuid'] = 1000;
        $GLOBALS['posix_getgid'] = 1000;
        $GLOBALS['posix_getpwuid'] = true;
        $GLOBALS['posix_getgrgid'] = true;
        $GLOBALS['pcntl_signal'] = array();
    }

    protected function tearDown()
    {
        $this->setUp();
    }


    /**
     * @covers Arara\Process\Item::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Callback given is not a valid callable
     */
    public function testShouldThrowsAnExceptionIfCallbackIsNotCallable()
    {
        new Item(new \stdClass());
    }

    /**
     * @covers Arara\Process\Item::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The given UID "159789" is not valid
     */
    public function testShouldThrowsAnExceptionIfUserIdIsNotValid()
    {
        $GLOBALS['posix_getpwuid'] = false;
        new Item('trim', new ArrayIpc(), 159789);
    }

    /**
     * @covers Arara\Process\Item::__construct
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The given GID "987159" is not valid
     */
    public function testShouldThrowsAnExceptionIfGroupIdIsNotValid()
    {
        $GLOBALS['posix_getgrgid'] = false;
        new Item('trim', new ArrayIpc(), 159789, 987159);
    }

    /**
     * @covers Arara\Process\Item::__construct
     */
    public function testShouldDefinePropertiesOnConstructor()
    {
        $callback   = function () {};
        $ipc        = new ArrayIpc();
        $uid        = 1024;
        $gid        = 1024;

        $item = new Item($callback, $ipc, $uid, $gid);

        $this->assertAttributeSame($callback, 'callback', $item);
        $this->assertAttributeSame($ipc, 'ipc', $item);
        $this->assertAttributeSame($uid, 'userId', $item);
        $this->assertAttributeSame($gid, 'groupId', $item);
    }

    /**
     * @depends testShouldDefinePropertiesOnConstructor
     * @covers Arara\Process\Item::__construct
     * @covers Arara\Process\Item::getIpc
     * @covers Arara\Process\Item::getUserId
     * @covers Arara\Process\Item::getGroupId
     * @covers Arara\Process\Item::getCallback
     */
    public function testShouldRetrieveDefinedPropertiesOnConstructor()
    {
        $callback   = function () {};
        $ipc        = new ArrayIpc();
        $uid        = 1024;
        $gid        = 1024;

        $item = new Item($callback, $ipc, $uid, $gid);

        $this->assertSame($callback, $item->getCallback());
        $this->assertSame($ipc, $item->getIpc());
        $this->assertSame($uid, $item->getUserId());
        $this->assertSame($gid, $item->getGroupId());
    }

    /**
     * @covers Arara\Process\Item::isRunning
     */
    public function testShouldDefineAsNotRunningInTheBegining()
    {
        $ipc = new ArrayIpc();

        $item = new Item('trim', $ipc);

        $this->assertFalse($item->isRunning());
    }

    /**
     * @covers Arara\Process\Item::start
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to fork process
     */
    public function testShouldThrowsAnExceptionWhenCanNotFork()
    {
        $ipc = new ArrayIpc();
        $item = new Item('trim', $ipc);
        $GLOBALS['pcntl_fork'] = -1;
        $item->start(new SignalHandler());
    }

    /**
     * @covers Arara\Process\Item::start
     * @covers Arara\Process\Item::getPid
     * @covers Arara\Process\Item::isRunning
     */
    public function testShouldMarkAsRunningAndStorePidOnParentAfterFork()
    {
        $ipc = new ArrayIpc();
        $item = new Item('trim', $ipc);
        $GLOBALS['pcntl_fork'] = 159;
        $item->start(new SignalHandler());

        $this->assertSame($GLOBALS['pcntl_fork'], $item->getPid());
        $this->assertTrue($item->isRunning());
    }

    /**
     * @covers Arara\Process\Item::start
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Process already forked
     */
    public function testShouldThrowsAnExceptionWhenTryingToForkMoreThanOce()
    {
        $ipc = new ArrayIpc();
        $item = new Item('trim', $ipc);
        $GLOBALS['pcntl_fork'] = 159;
        $item->start(new SignalHandler());

        // Second time
        $item->start(new SignalHandler());
    }

    /**
     * @covers Arara\Process\Item::start
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to fork process as "1000:1000". "1001:1001" given
     */
    public function testShouldThrowsAnExceptionIfNotAbleToForkAsAnUser()
    {
        $GLOBALS['pcntl_fork'] = 0;

        $ipc = new ArrayIpc();
        $item = new Item('trim', $ipc, 1000, 1000);

        $GLOBALS['posix_getuid'] = 1001;
        $GLOBALS['posix_getgid'] = 1001;

        $item->start(new SignalHandler());
    }

    /**
     * @covers Arara\Process\Item::start
     * @covers Arara\Process\Item::isSuccessful
     * @covers Arara\Process\Item::getStatus
     * @covers Arara\Process\Item::getResult
     * @covers Arara\Process\Item::getOutput
     */
    public function testShouldRunSuccessfulProcess()
    {
        $GLOBALS['pcntl_fork'] = 0;

        $successful = true;
        $status     = Item::STATUS_SUCESS;
        $result     = 'This is the result';
        $output     = 'This is the output';
        $callback   = function () use ($result, $output) {
            echo $output;
            return $result;
        };

        $ipc = new ArrayIpc();
        $item = new Item($callback, $ipc, 1000, 1000);

        $GLOBALS['posix_getuid'] = 1000;
        $GLOBALS['posix_getgid'] = 1000;

        $signalHandler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();

        $signalHandler
            ->expects($this->once())
            ->method('quit')
            ->with(0);

        $item->start($signalHandler);

        $this->assertSame($successful, $item->isSuccessful());
        $this->assertSame($status, $item->getStatus());
        $this->assertSame($result, $item->getResult());
        $this->assertSame($output, $item->getOutput());
    }

    /**
     * @covers Arara\Process\Item::start
     * @covers Arara\Process\Item::isSuccessful
     * @covers Arara\Process\Item::getStatus
     * @covers Arara\Process\Item::getResult
     * @covers Arara\Process\Item::getOutput
     */
    public function testShouldRunProcessWithPHPErros()
    {
        $GLOBALS['pcntl_fork'] = 0;

        $successful = false;
        $status     = Item::STATUS_FAIL;
        $result     = 'array_combine() expects parameter 1 to be array, string given';
        $callback   = function () use ($result) {
            array_combine('String', 'String');
            return $result;
        };

        $ipc = new ArrayIpc();
        $item = new Item($callback, $ipc, 1000, 1000);

        $GLOBALS['posix_getuid'] = 1000;
        $GLOBALS['posix_getgid'] = 1000;

        $signalHandler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();

        $signalHandler
            ->expects($this->once())
            ->method('quit')
            ->with(E_WARNING);

        $item->start($signalHandler);

        $this->assertSame($successful, $item->isSuccessful());
        $this->assertSame($status, $item->getStatus());
        $this->assertSame($result, $item->getResult());
        $this->assertSame('', $item->getOutput());
    }

    /**
     * @covers Arara\Process\Item::start
     * @covers Arara\Process\Item::isSuccessful
     * @covers Arara\Process\Item::getStatus
     * @covers Arara\Process\Item::getResult
     * @covers Arara\Process\Item::getOutput
     */
    public function testShouldRunProcessWithExceptions()
    {
        $GLOBALS['pcntl_fork'] = 0;

        $successful = false;
        $code       = 101;
        $status     = Item::STATUS_ERROR;
        $result     = 'This is the exception message';
        $output     = '';
        $callback   = function () use ($result, $code) {
            throw new \Exception($result, $code);
        };

        $ipc = new ArrayIpc();
        $item = new Item($callback, $ipc, 1000, 1000);

        $GLOBALS['posix_getuid'] = 1000;
        $GLOBALS['posix_getgid'] = 1000;

        $signalHandler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();

        $signalHandler
            ->expects($this->once())
            ->method('quit')
            ->with($code);

        $item->start($signalHandler);

        $this->assertSame($successful, $item->isSuccessful());
        $this->assertSame($status, $item->getStatus());
        $this->assertSame($result, $item->getResult());
        $this->assertSame('', $item->getOutput());
    }


}
