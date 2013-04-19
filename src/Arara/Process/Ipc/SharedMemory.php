<?php

namespace Arara\Process\Ipc;


class SharedMemory implements Ipc
{

    private $id;
    private $data = array();
    private static $number = 1;


    public function __construct()
    {
        $this->id = @shmop_open(self::$number++, 'c', 0777, 1024);
        if (false === $this->id) {
            $message = 'Could not create shared memory segment';
            throw new \RuntimeException($message);
        }
        $this->size = shmop_size($this->id);
    }

    public function save($name, $value)
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

    public function load($name)
    {
        $loaded = @shmop_read($this->id, 0, $this->size);
        if (false === $loaded) {
            $message = 'Could not read from shared memory block';
            throw new \RuntimeException($message);
        }

        $data = @unserialize($loaded);
        if (!is_array($data)) {
            $data = array();
        }
        $this->data = $data;

        if (!array_key_exists($name, $this->data)) {
            return null;
        }

        return $this->data[$name];
    }

    public function destroy()
    {
        @shmop_delete($this->id);

        return $this;
    }

    public function __destruct()
    {
        @shmop_close($this->id);
    }


}

