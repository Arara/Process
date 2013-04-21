<?php

namespace Arara\Process\Ipc;

class File implements Ipc
{

    private $filename;

    public function __construct($dirname = null)
    {
        if (null === $dirname) {
            $dirname = sys_get_temp_dir();
        }

        if (!is_string($dirname) || (is_string($dirname) && !is_dir($dirname))) {
            $message = '"%s" is not a valid directory';
            throw new \InvalidArgumentException(sprintf($message, print_r($dirname, true)));
        }

        if (!is_writable($dirname)) {
            $message = '"%s" is not writable';
            throw new \InvalidArgumentException(sprintf($message, print_r($dirname, true)));
        }

        $filename = $dirname . '/' . uniqid('arara_') . '.ipc';

        touch($filename);
        chmod($filename, 0777);

        $this->filename = $filename;
    }

    public function destroy()
    {
        unlink($this->filename);
    }

    public function getData()
    {
        if (!is_file($this->filename)) {
            return array();
        }

        $content    = file_get_contents($this->filename);
        $data       = @unserialize($content);
        if (false === $data) {
            return array();
        }

        return $data;
    }

    public function save($name, $value)
    {
        $data = $this->getData();
        $data[$name] = $value;

        file_put_contents($this->filename, serialize($data), LOCK_EX);

        return $this;
    }

    public function load($name)
    {
        $data = $this->getData();
        $value = null;
        if (isset($data[$name])) {
            $value = $data[$name];
        }

        return $value;
    }

}
