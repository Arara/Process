<?php

/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process;

use Arara\Process\Exception\RuntimeException;

/**
 * Interface for processes.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
interface Process
{
    /**
     * Return TRUE when process is running or FALSE if not.
     *
     * @return bool
     */
    public function isRunning();

    /**
     * Kill the current process (SIGKILL).
     *
     * @return bool If could kill the process or not.
     */
    public function kill();

    /**
     * Start the process.
     *
     * Define the process id for the parent and child and also mark the object
     * as parent or child.
     *
     * @throws RuntimeException If could not start the process.
     */
    public function start();

    /**
     * Terminate the current process (SIGTERM).
     *
     * @return bool If could terminate the process or not.
     */
    public function terminate();

    /**
     * Wait the current process.
     *
     * @return bool If none error occouts when waiting the process or any.
     */
    public function wait();
}
