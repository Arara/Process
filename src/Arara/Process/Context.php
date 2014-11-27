<?php

namespace Arara\Process;

/**
 * @property bool $isRunning
 * @property Exception $exception
 * @property int $exitCode
 * @property int $finishTime
 * @property int $processId
 * @property int $startTime
 * @property int $timeout
 * @property resource $stdin
 * @property resource $stdout
 * @property resource $stderr
 * @property Pidfile $pidfile
 * @property string $command
 * @property string $outputTail
 * @property string $outputString
 * @property array $outputLines
 * @property int $returnValue
 */
class Context
{
    protected $data = array();

    /**
     * Accept an array of properties on constructor
     *
     * @param  array $data Key => value properties
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $property => $value) {
            $this->__set($property, $value);
        }
    }

    /**
     * Defines a value for some property.
     *
     * @param  string $property
     * @param  mixed $value
     */
    public function __set($property, $value)
    {
        $this->data[$property] = $value;
    }

    /**
     * Returns the value of a property.
     *
     * If property does not exists, returns NULL.
     *
     * @return mixed
     */
    public function __get($property)
    {
        $value = null;
        if (isset($this->data[$property])) {
            $value = $this->data[$property];
        }

        return $value;
    }

    /**
     * Returns all data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = array();
        foreach ($this->data as $key => $value) {
            if ($value instanceof \Exception) {
                $value = array(
                    'class'     => get_class($value),
                    'message'   => $value->getMessage(),
                    'code'      => $value->getCode(),
                    'file'      => $value->getFile(),
                    'line'      => $value->getLine(),
                );
            }
            $data[$key] = $value;
        }

        return $data;
    }
}
