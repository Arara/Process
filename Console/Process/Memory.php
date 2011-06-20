<?php

/**
 * @namespace
 */

namespace Console\Process;

/**
 * Handles Shared Memoty data.
 *
 * @package    Console
 * @subpackage Console\Process
 * @author     Henrique Moody <henriquemoody@gmail.com>
 */
class Memory
{

    /**
     * Id of the shared memory block.
     *
     * @var int
     */
    private $_id;

    /**
     * The number of bytes the shared memory block occupies.
     *
     * @var int
     */
    private $_size;

    /**
     * Data in the memory.
     *
     * @var array
     */
    private $_data = array();

    /**
     * Constructor.
     *
     * Creates a shared memory block.
     *
     * @author  Henrique Moody <henriquemoody@gmail.com>
     */
    public function __construct()
    {
        if (!function_exists('shmop_open')) {
            $message = 'shmop_* functions are required';
            throw new \UnexpectedValueException($message);
        }

        $this->_id = @shmop_open(spl_object_hash($this), 'c', 0644, 1024);
        if (!$this->_id) {
            $message = 'Could not create shared memory segment';
            throw new \RuntimeException($message);
        }
        // Get shared memory block's size
        $this->_size = shmop_size($this->_id);
    }

    /**
     * Writes $value with the key $name in the shared memory.
     *
     * @param   string $name
     * @param   mixed $value
     * @return  Console\Process\Memory Fluent interface, returns self.
     */
    public function write($name, $value)
    {
        $this->_data[$name] = $value;

        $data = serialize($this->_data);

        $bytesWritten = shmop_write($this->_id, $data, 0);
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
        $data = shmop_read($this->_id, 0, $this->_size);
        if (!$data) {
            $message = 'Could not read from shared memory block';
            throw new \RuntimeException($message);
        }

        $data = unserialize($data);
        if (!is_array($data)) {
            $data = array();
        }
        $this->_data = $data;

        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
    }

    /**
     * Cleans the data of the shared memory.
     *
     * @return  Console\Process\Memory Fluent interface, returns self.
     */
    public function clean()
    {
        if (!shmop_delete($this->_id)) {
            $message = 'Could not mark shared memory block for deletion';
            throw new \RuntimeException($message);
        }
        return $this;
    }

    /**
     * Destructor.
     *
     * Closes the shared memory connection.
     */
    public function __destruct()
    {
        shmop_close($this->_id);
    }


}

