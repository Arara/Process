<?php

namespace Arara\Process;

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
     * @throws RuntimeException If could not kill the process.
     * @return void
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
     * @throws RuntimeException If could not terminate the process.
     * @return void
     */
    public function terminate();

    /**
     * Wait the current process.
     *
     * @throws RuntimeException If an error occouts when waiting the process.
     * @return void
     */
    public function wait();
}
