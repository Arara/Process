<?php

namespace Arara\Process\Ipc;

class Arr implements Ipc
{
    private $data = array();

    public function save($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function load($name)
    {
        if (! isset($this->data[$name])) {
            return null;
        }

        return $this->data[$name];
    }

    public function destroy()
    {
        $this->data = array();
    }
}
