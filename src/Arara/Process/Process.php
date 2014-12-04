<?php

namespace Arara\Process;

use Arara\Process\Exception\RuntimeException;

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
     * @return boolean If could kill the process or not.
     */
    public function kill();

    /**
     * Start the process.
     *
     * Define the process id for the parent and child and also mark the object
     * as parent or child.
     *
     * @throws RuntimeException If could not start the process.
     * @return void
     */
    public function start();

    /**
     * Terminate the current process (SIGTERM).
     *
     * @return boolean If could terminate the process or not.
     */
    public function terminate();

    /**
     * Wait the current process.
     *
     * @return boolean If none error occouts when waiting the process or any.
     */
    public function wait();
}
