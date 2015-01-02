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

use Arara\Process\Control;

/**
 * Abstract implementation for signal handlers.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
abstract class SignalAbstract
{
    /**
     * @var Control
     */
    protected $control;

    /**
     * @param Control $control
     */
    public function __construct(Control $control)
    {
        $this->control = $control;
    }

    /**
     * Handler for the signal.
     *
     * @param integer $signal
     *
     * @return null
     */
    abstract public function __invoke($signal);
}
