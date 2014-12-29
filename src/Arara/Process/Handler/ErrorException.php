<?php

namespace Arara\Process\Handler;

use Arara\Process\Exception\ErrorException as Exception;

/**
 * Handles PHP errors.
 */
class ErrorException
{
    /**
     * Handler for PHP errors.
     *
     * Always throws an exception.
     *
     * @param integer $severity
     * @param string  $message
     * @param string  $filename
     * @param integer $line
     *
     * @throws Exception
     *
     * @return null
     */
    public function __invoke($severity, $message, $filename, $line)
    {
        throw new Exception($message, 0, $severity, $filename, $line);
    }
}
