<?php

namespace Arara\Process\Handler;

class ErrorException
{
    public function __invoke($severity, $message, $filename, $line)
    {
        throw new \ErrorException($message, 0, $severity, $filename, $line);
    }
}
