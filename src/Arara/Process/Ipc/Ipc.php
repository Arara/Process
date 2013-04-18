<?php

namespace Arara\Process\Ipc;

interface Ipc
{

    public function save($name, $value);

    public function load($name);

    public function destroy();
}
