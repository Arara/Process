<?php

namespace Arara\Process\IPC ;

/**
 * This class provides Interprocess communication using UNIX Semaphores
 *
 * Code written by Nikolay and copied into Arara/Process under the terms of
 * the MIT license.
 *
 * Full credit to Nikolay for his excellent class
 * 
 * @license http://opensource.org/licenses/MIT
 * @author Nikolay Bondarenko http://misterion.ru 
 */
class Semaphore
{
    /**
     * @var resource
     */
    protected $mutex;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var bool
     */
    protected $isAcquired;

    /**
     * Creator process pid
     *
     * @var int
     */
    protected $pid;

    public function __construct($key = null)
    {
        $this->pid = getmypid();

        $this->isAcquired = false;
        if ($key) {
            $semKey = $key;
        } else {
            $this->file = tempnam(sys_get_temp_dir(), 's');
            $semKey = ftok($this->file, 'a');
        }

        $this->mutex = sem_get($semKey, 1); //auto_release = 1 by default
        if (!$this->mutex) {
            throw new \RuntimeException('Unable to create the semaphore');
        }
    }

    public function __destruct()
    {
        if ($this->isAcquired()) {
            $this->release();
        }

        if ($this->pid === getmypid()) {
            $this->remove();
        }
    }

    /**
     * Remove semaphore.
     */
    public function remove()
    {
        if (is_resource($this->mutex)) {
            sem_remove($this->mutex);
        }

        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * Return TRUE if semaphore acquired in this process.
     *
     * @return boolean
     */
    public function isAcquired()
    {
        return $this->isAcquired;
    }

    /**
     * Release a semaphore.
     *
     * @return bool
     */
    public function release()
    {
        $this->isAcquired = !sem_release($this->mutex);

        return $this->isAcquired;
    }

    /**
     * Lock and execute given callable.
     *
     * @param callable $callable
     *
     * @return mixed
     */
    public function lockExecute(callable $callable)
    {
        $this->isAcquired = $this->acquire();
        $result = $callable();
        $this->isAcquired = $this->release();

        return $result;
    }

    /**
     * Acquire a semaphore.
     *
     * @return bool
     */
    public function acquire()
    {
        $this->isAcquired = sem_acquire($this->mutex);

        return $this->isAcquired;
    }
}