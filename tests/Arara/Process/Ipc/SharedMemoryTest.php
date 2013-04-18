<?php

namespace Arara\Process\Ipc;

function shmop_open()
{
    return $GLOBALS['shmop_open'];
}

function shmop_write($shmid, $data, $offset)
{
    $GLOBALS['shmop_data'][$shmid] = $data;
    $GLOBALS['shmop_data'][$shmid] = $data;

    return $GLOBALS['shmop_write'];
}

function shmop_read($shmid, $start, $count)
{
    if (array_key_exists('shmop_read', $GLOBALS)) {
        return $GLOBALS['shmop_read'];
    }

    if (array_key_exists($shmid, $GLOBALS['shmop_data'])) {
        return $GLOBALS['shmop_data'][$shmid];
    }

    return null;
}

function shmop_delete($shmid)
{
    unset($GLOBALS['shmop_data'][$shmid]);
}
function shmop_size($shmid)
{
}


class SharedMemoryTest extends \PHPUnit_Framework_TestCase
{


    protected function setUp()
    {
        $GLOBALS['shmop_open'] = null;
        $GLOBALS['shmop_data'] = array();
        $GLOBALS['shmop_write'] = 0;
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::__construct
     * @covers Arara\Process\Ipc\SharedMemory::__destruct
     */
    public function testShouldOpenConnectionOnContructor()
    {
        $GLOBALS['shmop_open'] = 123;
        $ipc = new SharedMemory();

        $this->assertAttributeSame(123, 'id', $ipc);
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::__construct
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not create shared memory segment
     */
    public function testShouldThrowsAnExceptionIsCouldNotOpenConnectionOnContructor()
    {
        $GLOBALS['shmop_open'] = false;
        new SharedMemory();
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::save
     */
    public function testShouldSaveData()
    {
        $GLOBALS['shmop_open'] = 1988;
        $ipc = new SharedMemory();
        $GLOBALS['shmop_write'] = strlen(serialize(array('a' => '1')));
        $ipc->save('a', '1');
        $GLOBALS['shmop_write'] = strlen(serialize(array('a' => '1', 'b' => 2)));
        $ipc->save('b', 2);

        $expected = array(
            $GLOBALS['shmop_open'] => serialize(array('a' => '1', 'b' => 2))
        );
        $this->assertSame($expected, $GLOBALS['shmop_data']);
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::save
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not write the entire length of data
     */
    public function testShouldThrowsAnExceptionIfCouldNotWrite()
    {
        $GLOBALS['shmop_open'] = 1988;
        $ipc = new SharedMemory();
        $GLOBALS['shmop_write'] = strlen(serialize(array('a' => '1')));
        $ipc->save('a', '1');
        $GLOBALS['shmop_write'] = strlen(serialize(array('a' => '1')));
        $ipc->save('b', 2);
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::load
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not read from shared memory block
     */
    public function testShouldThrowsAnExceptionIfCouldNotRead()
    {
        $ipc = new SharedMemory();
        $GLOBALS['shmop_read'] = false;
        $GLOBALS['shmop_data'][159] = false;
        $ipc->load('asd');
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::load
     */
    public function testShouldReturnNullIfValueWasNotFound()
    {
        $ipc = new SharedMemory();

        $this->assertNull($ipc->load('asd'));
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::load
     */
    public function testShouldReturnValidValueValueWhenFound()
    {
        $GLOBALS['shmop_open'] = 1988;
        $ipc = new SharedMemory();
        $GLOBALS['shmop_write'] = strlen(serialize(array('asd' => 159)));
        $ipc->save('asd', 159);

        $this->assertSame(159, $ipc->load('asd'));
    }

    /**
     * @covers Arara\Process\Ipc\SharedMemory::destroy
     */
    public function testShouldDestroyAllData()
    {
        $GLOBALS['shmop_open'] = 1988;
        $ipc = new SharedMemory();
        $GLOBALS['shmop_write'] = strlen(serialize(array('asd' => 159)));
        $ipc->save('asd', 159);

        $GLOBALS['shmop_open'] = 1989;
        $ipc2 = new SharedMemory();
        $GLOBALS['shmop_write'] = strlen(serialize(array('asd' => 789)));
        $ipc2->save('asd', 789);
        $ipc2->destroy();

        $this->assertArrayNotHasKey(1989, $GLOBALS['shmop_data']);
    }


}
