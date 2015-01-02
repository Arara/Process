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

use Exception;

/**
 * Used as data context when any event is triggered.
 *
 * @property string     $command
 * @property Exception  $exception
 * @property integer    $exitCode
 * @property integer    $finishTime
 * @property boolean    $isRunning
 * @property array      $outputLines
 * @property string     $outputString
 * @property string     $outputTail
 * @property Pidfile    $pidfile
 * @property integer    $processId
 * @property integer    $returnValue
 * @property integer    $startTime
 * @property resource   $stderr
 * @property resource   $stdin
 * @property resource   $stdout
 * @property integer    $timeout
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Context
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * Accept an array of properties on constructor.
     *
     * @param array $data Optional key => value properties.
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
     * @param string $property
     * @param mixed  $value
     *
     * @return null
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
     * Normalizes the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function normalize($value)
    {
        if ($value instanceof Exception) {
            $value = array(
                'class'     => get_class($value),
                'message'   => $value->getMessage(),
                'code'      => $value->getCode(),
                'file'      => $value->getFile(),
                'line'      => $value->getLine(),
            );
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
            $data[$key] = $this->normalize($value);
        }

        return $data;
    }
}
