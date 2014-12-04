<?php

namespace Arara\Process\Handler;

use Arara\Process\Exception\ErrorException as Exception;

class ErrorException
{
    public function __invoke($severity, $message, $filename, $line)
    {
        throw new Exception($message, 0, $severity, $filename, $line);
    }
}
