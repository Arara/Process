<?php
/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;
use Arara\Process\Exception\RuntimeException;

/**
 * Action implementation for shell commands.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Command extends Callback
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $arguments = array();

    /**
     * @param string  $command
     * @param array   $arguments
     * @param boolean $prefix
     */
    public function __construct($command, array $arguments = array(), $prefix = true)
    {
        $this->command = $prefix ? '/usr/bin/env '.$command : $command;
        $this->arguments = $arguments;
    }

    /**
     * Gets the value of command.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Gets the value of arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Assemble command and arguments.
     *
     * @return string
     */
    protected function assemble()
    {
        $assembled = $this->command;
        foreach ($this->arguments as $key => $value) {
            if (is_int($key)
                && false === strpos((string) $key, '-')) {
                $assembled .= ' '.escapeshellarg($value);
                continue;
            }
            $assembled .= ' '.escapeshellarg($key);
            $assembled .= ' '.escapeshellarg($value);
        }

        return $assembled;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Control $control, Context $context)
    {
        $context->command       = $this->assemble();
        $context->outputTail    = exec(sprintf('(%s)2>&1', $context->command), $outputLines, $returnValue);
        $context->outputString  = implode(PHP_EOL, $outputLines);
        $context->outputLines   = $outputLines;
        $context->returnValue   = $returnValue;

        if ($returnValue > 0) {
            throw new RuntimeException($context->outputTail);
        }
    }
}
