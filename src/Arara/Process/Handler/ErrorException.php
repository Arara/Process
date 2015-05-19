<?php
/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Handler;

use Arara\Process\Exception\ErrorException as Exception;

/**
 * Handles PHP errors.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
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
        if (0 === error_reporting()) {
            return;
        }

        throw new Exception($message, 0, $severity, $filename, $line);
    }
}
