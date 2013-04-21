<?php

namespace Arara\Process\Ipc;

function uniqid($prefix = null) { return $prefix . $GLOBALS['uniqid']; }
function touch($filename) { $GLOBALS['touch'] = $filename; }
function unlink($filename) { $GLOBALS['unlink'] = $filename; }
function chmod($filename, $mode) { $GLOBALS['chmod_filename'] = $filename; $GLOBALS['chmod_mode'] = $mode; }
function is_dir() { return $GLOBALS['is_dir']; }
function is_writable() { return $GLOBALS['is_writable']; }
function file_get_contents() { return @$GLOBALS['file_contents']; }
function file_put_contents($filename, $content) { $GLOBALS['file_contents'] = $content; }
function is_file() { return $GLOBALS['is_file']; }

class FileTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $GLOBALS['uniqid'] = '39F';
        $GLOBALS['is_dir'] = true;
        $GLOBALS['is_file'] = true;
        $GLOBALS['is_writable'] = true;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "/root" is not a valid directory
     * @covers Arara\Process\Ipc\File::__construct
     */
    public function testShoudThrowsAnExceptionIfGivenDiretoryIsNotValid()
    {
        $GLOBALS['is_dir'] = false;

        new File('/root');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "/root" is not writable
     * @covers Arara\Process\Ipc\File::__construct
     */
    public function testShoudThrowsAnExceptionIfGivenDiretoryIsNotWritable()
    {
        $GLOBALS['is_writable'] = false;

        new File('/root');
    }

    /**
     * @covers Arara\Process\Ipc\File::__construct
     */
    public function testShoudCreateFile()
    {
        $ipc = new File('/root');
        $filename = '/root/arara_' . $GLOBALS['uniqid'] . '.ipc';

        $this->assertSame($filename, $GLOBALS['touch']);
        $this->assertSame($filename, $GLOBALS['chmod_filename']);
        $this->assertSame(0777, $GLOBALS['chmod_mode']);
        $this->assertAttributeSame($filename, 'filename', $ipc);
    }

    /**
     * @covers Arara\Process\Ipc\File::__construct
     * @depends testShoudCreateFile
     */
    public function testShouldUseTemporaryDirectoryByDefault()
    {
        $ipc = new File();
        $filename = sys_get_temp_dir() . '/arara_' . $GLOBALS['uniqid'] . '.ipc';

        $this->assertSame($filename, $GLOBALS['touch']);
    }


    /**
     * @covers Arara\Process\Ipc\File::destroy
     */
    public function testShoudDestroyFile()
    {
        $ipc = new File('/root');
        $ipc->destroy();

        $filename = '/root/arara_' . $GLOBALS['uniqid'] . '.ipc';

        $this->assertSame($filename, $GLOBALS['unlink']);
    }

    /**
     * @covers Arara\Process\Ipc\File::getData
     */
    public function testShoudReturnAllDataFile()
    {
        $ipc = new File('/root');

        $data = array(
            'foo' => 123,
            'bar' => 456,
            'baz' => 789,
        );

        $GLOBALS['file_contents'] = serialize($data);


        $this->assertSame($data, $ipc->getData());
    }

    /**
     * @covers Arara\Process\Ipc\File::getData
     * @depends testShoudReturnAllDataFile
     */
    public function testShoudReturnAnEmptyArrayIfFileDataIsNotValid()
    {
        $ipc = new File('/root');

        $data = array(
            'foo' => 123,
            'bar' => 456,
            'baz' => 789,
        );

        $GLOBALS['file_contents'] = __DIR__ . serialize($data);

        $this->assertSame(array(), $ipc->getData());
    }

    /**
     * @covers Arara\Process\Ipc\File::getData
     * @depends testShoudReturnAllDataFile
     */
    public function testShoudReturnAnEmptyArrayIfFileDoesNotExists()
    {
        $ipc = new File('/root');

        $GLOBALS['is_file'] = false;

        $this->assertSame(array(), $ipc->getData());
    }

    /**
     * @covers Arara\Process\Ipc\File::save
     * @depends testShoudReturnAllDataFile
     */
    public function testShouldSaveData()
    {
        $ipc = new File('/root');
        $ipc->save('foo', 123);
        $ipc->save('bar', 456);

        $this->assertSame(
            array('foo' => 123, 'bar' => 456),
            unserialize($GLOBALS['file_contents'])
        );
    }
    /**
     * @covers Arara\Process\Ipc\File::load
     * @depends testShouldSaveData
     */
    public function testShouldLoadData()
    {
        $ipc = new File('/root');
        $ipc->save('foo', 123);

        $this->assertSame(123, $ipc->load('foo'));
    }

}
