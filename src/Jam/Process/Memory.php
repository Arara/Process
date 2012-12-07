<?php

namespace Jam\Process;

/**
 * Handles shared memory data.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Memory
{

    /**
     * Id of the shared memory block.
     *
     * @var int
     */
    private $id;

    /**
     * Incremental variable to define shared memory ID's.
     *
     * @var int
     */
    private static $incremental = 1000;

    /**
     * The number of bytes the shared memory block occupies.
     *
     * @var int
     */
    private $size;

    /**
     * Data in the memory.
     *
     * @var array
     */
    private $data = array();

    /**
     * Constructor.
     *
     * Creates a shared memory block.
     */
    public function __construct()
    {
        $this->id  = @shmop_open(++self::$incremental, 'c', 0777, 1024);
        if (!$this->id) {
            $message = 'Could not create shared memory segment';
            throw new \RuntimeException($message);
        }
        // Get shared memory block's size
        $this->size = shmop_size($this->id);
    }

    /**
     * Writes $value with the key $name in the shared memory.
     *
     * @param   string $name
     * @param   mixed $value
     * @return  Jam\Process\Memory Fluent interface, returns self.
     */
    public function write($name, $value)
    {
        $this->data[$name] = $value;

        $data = @serialize($this->data);

        $bytesWritten = @shmop_write($this->id, $data, 0);
        if ($bytesWritten != strlen($data)) {
            $message = 'Could not write the entire length of data';
            throw new \RuntimeException($message);
        }
        return $this;
    }

    /**
     * returns the value of the key $name in the shared memory, if any.
     *
     * @param   string $name
     * @return  mixed If there is no data, returns null.
     */
    public function read($name)
    {
        // Now lets read the string back
        $data = @shmop_read($this->id, 0, $this->_size);
        if (!$data) {
            $message = 'Could not read from shared memory block';
            throw new \RuntimeException($message);
        }

        $data = @unserialize($data);
        if (!is_array($data)) {
            $data = array();
        }
        $this->data = $data;

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    /**
     * Cleans the data of the shared memory.
     *
     * @return  Jam\Process\Memory Fluent interface, returns self.
     */
    public function clean()
    {
        @shmop_delete($this->id);
        return $this;
    }

    /**
     * Destructor.
     *
     * Closes the shared memory connection.
     */
    public function __destruct()
    {
        @shmop_close($this->id);
    }


}

