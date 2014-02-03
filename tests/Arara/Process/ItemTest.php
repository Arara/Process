<?php

namespace Arara\Process;

function pcntl_fork() { return $GLOBALS['pcntl_fork']; }
function pcntl_setpriority() { return $GLOBALS['pcntl_setpriority']; }
function pcntl_waitpid($pid, &$status) { $status = $GLOBALS['pcntl_waitpid']; }
function posix_getgid() { return $GLOBALS['posix_getgid']; }
function posix_getgrgid() { return $GLOBALS['posix_getgrgid']; }
function posix_getpwuid() { return $GLOBALS['posix_getpwuid']; }
function posix_getuid() { return $GLOBALS['posix_getuid']; }
function posix_kill() { return $GLOBALS['posix_kill']; }

class ArrayIpc implements Ipc\Ipc
{
    public $data = array();
    public function save($name, $value) { $this->data[$name] = $value; }
    public function load($name) { if (isset($this->data[$name])) { return $this->data[$name]; } }
    public function destroy() { $this->data = array(); }

}


class ItemTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $GLOBALS['pcntl_setpriority'] = true;
        $GLOBALS['pcntl_signal'] = array();
        $GLOBALS['pcntl_waitpid'] = 0;
        $GLOBALS['posix_getgid'] = 1000;
        $GLOBALS['posix_getgrgid'] = true;
        $GLOBALS['posix_getpwuid'] = true;
        $GLOBALS['posix_getuid'] = 1000;
        $GLOBALS['posix_kill'] = true;
    }

    protected function tearDown()
    {
        $this->setUp();
    }

    /**
     * @covers Arara\Process\Item::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Action must be a valid callback
     */
    public function testShouldThrowsAnExceptionIfCallbackIsNotCallable()
    {
        new Item(new \stdClass(), new ArrayIpc());
    }

    /**
     * @covers Arara\Process\Item::getPid
     * @expectedException UnderflowException
     * @expectedExceptionMessage There is not defined process
     */
    public function testShouldThrowsAnExceptionIfPidIsNotDefined()
    {
        $item = new Item(function () {}, new ArrayIpc());
        $item->getPid();
    }

    /**
     * @covers Arara\Process\Item::getPid
     * @covers Arara\Process\Item::hasPid
     */
    public function testShouldDefineAPid()
    {
        $GLOBALS['pcntl_fork'] = 7230;

        $item = new Item(function () {}, new ArrayIpc());
        $signalHandler = new SignalHandler();

        $this->assertFalse($item->hasPid());
        $item->start($signalHandler);
        $this->assertSame($GLOBALS['pcntl_fork'], $item->getPid());
        $this->asserttrue($item->hasPid());
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

        $this->assertAttributeSame($callback, 'action', $item);
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
     */
    public function testShouldRetrieveDefinedPropertiesOnConstructor()
    {
        $callback   = function () {};
        $ipc        = new ArrayIpc();
        $uid        = 1024;
        $gid        = 1024;

        $item = new Item($callback, $ipc, $uid, $gid);

        $this->assertSame($ipc, $item->getIpc());
        $this->assertSame($uid, $item->getUserId());
        $this->assertSame($gid, $item->getGroupId());
    }

    /**
     * @covers Arara\Process\Item::setCallback
     * @covers Arara\Process\Item::getCallback
     */
    public function testShouldDefineAndRestrieveASimpleCallbackType()
    {
        $action = function () {};
        $success = function () {};
        $error = function () {};
        $fail = function () {};
        $item = new Item($action, new ArrayIpc());
        $item->setCallback($success, Item::STATUS_SUCESS);
        $item->setCallback($error, Item::STATUS_ERROR);
        $item->setCallback($fail, Item::STATUS_FAIL);

        $this->assertSame($success, $item->getCallback(Item::STATUS_SUCESS));
        $this->assertSame($error, $item->getCallback(Item::STATUS_ERROR));
        $this->assertSame($fail, $item->getCallback(Item::STATUS_FAIL));
    }

    /**
     * @covers Arara\Process\Item::setCallback
     * @covers Arara\Process\Item::getCallback
     */
    public function testShouldDefineAndRestrieveACombinedCallbackType()
    {
        $callback = function () {};
        $item = new Item(function () {}, new ArrayIpc());
        $item->setCallback($callback, Item::STATUS_ERROR | Item::STATUS_FAIL);

        $this->assertSame($callback, $item->getCallback(Item::STATUS_FAIL));
        $this->assertSame($callback, $item->getCallback(Item::STATUS_ERROR));
    }

    /**
     * @covers Arara\Process\Item::getCallback
     */
    public function testShouldReturnAValidCallbackByDefault()
    {
        $item = new Item(function () {}, new ArrayIpc());

        $this->assertTrue(is_callable($item->getCallback(Item::STATUS_FAIL)));
    }

    /**
     * @covers Arara\Process\Item::setCallback
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Callback given is not a valid callable
     */
    public function testShouldThrowsAnExceptionWhenDefiningAnInvalidCallback()
    {
        $item = new Item(function () {}, new ArrayIpc());
        $item->setCallback(array(), Item::STATUS_SUCESS);
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
     */
    public function testShouldReturnFalseWhenCanNotFork()
    {
        $ipc = new ArrayIpc();
        $item = new Item('trim', $ipc);
        $GLOBALS['pcntl_fork'] = -1;

        $this->assertFalse($item->start(new SignalHandler()));
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
     * @expectedException UnderflowException
     * @expectedExceptionMessage Process already started
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
     */
    public function testShouldThrowsAnExceptionIfNotAbleToForkAsAnUser()
    {
        $GLOBALS['pcntl_fork'] = 0;

        $ipc = new ArrayIpc();
        $item = new Item('trim', $ipc, 1000, 1000);

        $GLOBALS['posix_getuid'] = 1001;
        $GLOBALS['posix_getgid'] = 1001;


        $signalHandler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();

        $signalHandler
            ->expects($this->once())
            ->method('quit')
            ->with(2);

        $item->start($signalHandler);

        $this->assertInstanceOf('RuntimeException', $ipc->load('result'));
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
        $callback   = function () {
            array_combine('String', 'String');
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
            ->with(1);

        $item->start($signalHandler);

        $this->assertSame($successful, $item->isSuccessful());
        $this->assertSame($status, $item->getStatus());
        $this->assertInstanceOf('ErrorException', $item->getResult());
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
        $status     = Item::STATUS_ERROR;
        $output     = '';
        $exception  = new \Exception('This is the exception message');
        $callback   = function () use ($exception) {
            throw $exception;
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
            ->with(2);

        $item->start($signalHandler);

        $this->assertSame($successful, $item->isSuccessful());
        $this->assertSame($status, $item->getStatus());
        $this->assertSame($exception, $item->getResult());
        $this->assertSame('', $item->getOutput());
    }


    /**
     * @covers Arara\Process\Item::wait
     */
    public function testShouldWaitAProcess()
    {
        $GLOBALS['pcntl_fork'] = 7230;
        $GLOBALS['pcntl_waitpid'] = -1;

        $item = new Item(function () {}, new ArrayIpc());
        $signalHandler = new SignalHandler();

        $item->start($signalHandler);
        $this->assertSame($GLOBALS['pcntl_waitpid'], $item->wait());
    }

    /**
     * @covers Arara\Process\Item::stop
     */
    public function testShouldStopAProcess()
    {
        $GLOBALS['pcntl_fork'] = 7230;
        $GLOBALS['posix_kill'] = true;

        $item = new Item(function () {}, new ArrayIpc());
        $signalHandler = new SignalHandler();

        $item->start($signalHandler);
        $this->assertTrue($item->stop());
    }

    /**
     * @covers Arara\Process\Item::setPriority
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to set the priority
     */
    public function testShouldThrowsAnExceptionIfCouldNotSetChildPriority()
    {
        $GLOBALS['pcntl_fork'] = 7230;

        $process = new Item(function () {}, new ArrayIpc());
        $process->start(new SignalHandler());

        $GLOBALS['pcntl_setpriority'] = false;
        $process->setPriority(10);
    }


}
