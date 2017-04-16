<?php

namespace Arara\Process\IPC ;

/**
 * This class provides Interprocess communication using UNIX Shared memory
 *
 * Code written by Nikolay and copied into Arara/Process under the terms of
 * the MIT license.
 *
 * Full credit to Nikolay for his excellent class
 * 
 * @license http://opensource.org/licenses/MIT
 * @author Nikolay Bondarenko http://misterion.ru 
 */
class SharedMemory implements \ArrayAccess, \Countable
{
    /**
     * @var resource
     */
    public $id;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var Semaphore
     */
    protected $mutex;

    /**
     * Creator process pid
     *
     * @var int
     */
    protected $pid;

    public function __construct($memorySize = 1024)
    {
        $this->pid = getmypid();

        $this->size = $memorySize;

        $this->file = tempnam(sys_get_temp_dir(), 's');
        $key = ftok($this->file, 'a');

        $this->id = shm_attach($key, $this->size);
        if (false === $this->id) {
            throw new \RuntimeException('Unable to create the shared memory segment');
        }

        //Keymapper (maps mixed to integers)
        shm_put_var($this->id, 0, []) ;
        $this->mutex = new Semaphore($key);
    }

    public function __destruct()
    {
        if ($this->pid === getmypid()) {
            unset($this->mutex); //Destroy mutex first because of we share same System V IPC key.
            $this->remove();
        }
    }

    public function remove()
    {
        if (is_resource($this->id)) {
            shm_remove($this->id);
        }

        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        $task = function() use ($offset) {
            return shm_has_var($this->id, $this->getKey($offset));
        } ;

        if($this->mutex->isAcquired())
            return $task() ;
        else
            return $this->mutex->lockExecute($task) ;
    }
    
    public function getKeys()
    {
        return array_keys($this->getKeyMap()) ;
    }

    protected function getKeyMap()
    {
        $task = function() {
            return shm_get_var($this->id, 0) ;
        } ;
        
        return $this->lockExecute($task) ;
    }
    
    protected function getKey($offset)
    {
        $keyMapper = $this->getKeyMap() ;
        if (isset($keyMapper[$offset])) {
            return $keyMapper[$offset];
        }

        //Get the next available integer for shm
        if(count($keyMapper) > 0)
            $keyMapper[$offset] = max($keyMapper)+1 ;
        else
            $keyMapper[$offset] = 1 ;
        shm_put_var($this->id, 0, $keyMapper);
        return $keyMapper[$offset] ;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        $task = function() use ($offset) {
            $key = $this->getKey($offset);
            if(shm_has_var($this->id, $key)) {
                return shm_get_var($this->id, $key);
            }

            return null;
        } ;

        return $this->lockExecute($task) ;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $task = function() use ($offset,$value) {
            shm_put_var($this->id, $this->getKey($offset), $value);
        } ;

        $this->lockExecute($task) ;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $task = function() use ($offset) {
            $keyMapper = $this->getKeyMap() ;
            if(array_key_exists($offset,$keyMapper)) {
                shm_remove_var($this->id, $keyMapper[$offset]) ;
                unset($keyMapper[$offset]) ;
                shm_put_var($this->id, 0, $keyMapper);
            }
        } ;
        
        $this->lockExecute($task) ;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {   $task = function() {
            return count($this->getKeyMap());
        } ;
        
        return $this->lockExecute($task) ;
    }
    
    /**
     * Lock shared memory if lock not already acquired by this process
     * 
     * @param function $task Function to execute
     * 
     * @return mixed    Whatever the $task function returns
     */
    protected function lockExecute($task)
    {
        if($this->mutex->isAcquired())
            return $task() ;
        else
            return $this->mutex->lockExecute($task) ;
    }
    
    /**
     * Lock shared memory
     * 
     * @return boolean    True if locked, otherwise false
     */
    public function lock()
    {
        return $this->mutex->acquire() ;
    }
    
    /**
     * Unlock shared memory
     * 
     * @return boolean    True if unlocked, otherwise false
     */
    public function release()
    {
        return $this->mutex->release() ;
    }
}