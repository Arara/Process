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

use Arara\Process\Exception\InvalidArgumentException;
use Arara\Process\Exception\RuntimeException;

/**
 * Class to handle pidfiles.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Pidfile
{
    /**
     * @var string
     */
    protected $applicationName;

    /**
     * @var Control
     */
    protected $control;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var resource
     */
    protected $fileResource;

    /**
     * @var string
     */
    protected $lockDirectory;

    /**
     * @var integer
     */
    protected $processId;

    /**
     * @param Control $control         Object used to control process information
     * @param string  $applicationName The application name, used as pidfile basename
     * @param string  $lockDirectory   Directory were pidfile is stored
     */
    public function __construct(Control $control, $applicationName = 'arara', $lockDirectory = '/var/run')
    {
        $this->control = $control;
        $this->setApplicationName($applicationName);
        $this->setLockDirectory($lockDirectory);
    }

    /**
     * Returns the application's name.
     *
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * Defines application name.
     *
     * @param string $applicationName The application name, used as pidfile basename.
     *
     * @throws InvalidArgumentException When application name is not valid.
     *
     * @return null
     */
    protected function setApplicationName($applicationName)
    {
        if ($applicationName != strtolower($applicationName)) {
            throw new InvalidArgumentException('Application name should be lowercase');
        }
        if (preg_match('/[^a-z0-9]/', $applicationName)) {
            throw new InvalidArgumentException('Application name should contains only alphanumeric chars');
        }

        if (strlen($applicationName) > 16) {
            $message = 'Application name should be no longer than 16 characters';
            throw new InvalidArgumentException($message);
        }

        $this->applicationName = $applicationName;
    }

    /**
     * Defines the lock directory.
     *
     * @param string $lockDirectory Directory were pidfile should be stored.
     *
     * @throws InvalidArgumentException When lock directory is not valid.
     *
     * @return null
     */
    protected function setLockDirectory($lockDirectory)
    {
        if (! is_dir($lockDirectory)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid directory', $lockDirectory));
        }

        if (! is_writable($lockDirectory)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a writable directory', $lockDirectory));
        }

        $this->lockDirectory = $lockDirectory;
    }

    /**
     * Returns the Pidfile filename
     *
     * @return string
     */
    protected function getFileName()
    {
        if (null === $this->fileName) {
            $this->fileName = $this->lockDirectory.'/'.$this->applicationName.'.pid';
        }

        return $this->fileName;
    }

    /**
     * Returns the Pidfile file resource
     *
     * @return resource
     */
    protected function getFileResource()
    {
        if (null === $this->fileResource) {
            $fileResource = @fopen($this->getFileName(), 'a+');
            if (! $fileResource) {
                throw new RuntimeException('Could not open pidfile');
            }
            $this->fileResource = $fileResource;
        }

        return $this->fileResource;
    }

    /**
     * Returns TRUE when pidfile is active or FALSE when is not.
     *
     * @return boolean
     */
    public function isActive()
    {
        $pid = $this->getProcessId();
        if (null === $pid) {
            return false;
        }

        return $this->control->signal()->send(0, $pid);
    }

    /**
     * Returns Pidfile content with the PID or NULL when there is no stored PID.
     *
     * @return integer|null
     */
    public function getProcessId()
    {
        if (null === $this->processId) {
            $content = fgets($this->getFileResource());
            $pieces = explode(PHP_EOL, trim($content));
            $this->processId = reset($pieces) ?: 0;
        }

        return $this->processId ?: null;
    }

    /**
     * Initializes pidfile.
     *
     * Create an empty file, store the PID into the file and lock it.
     *
     * @return null
     */
    public function initialize()
    {
        if ($this->isActive()) {
            throw new RuntimeException('Process is already active');
        }

        $handle = $this->getFileResource();

        if (! @flock($handle, (LOCK_EX | LOCK_NB))) {
            throw new RuntimeException('Could not lock pidfile');
        }

        if (-1 === @fseek($handle, 0)) {
            throw new RuntimeException('Could not seek pidfile cursor');
        }

        if (! @ftruncate($handle, 0)) {
            throw new RuntimeException('Could not truncate pidfile');
        }

        if (! @fwrite($handle, $this->control->info()->getId().PHP_EOL)) {
            throw new RuntimeException('Could not write on pidfile');
        }
    }

    /**
     * Finalizes pidfile.
     *
     * Unlock pidfile and removes it.
     *
     * @return null
     */
    public function finalize()
    {
        @flock($this->getFileResource(), LOCK_UN);
        @fclose($this->getFileResource());
        @unlink($this->getFileName());
    }
}
