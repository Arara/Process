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

/**
 * Handles the SIGQUIT signal.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class SignalQuit extends SignalAbstract
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($signal)
    {
        $this->control->quit(4);
    }
}
