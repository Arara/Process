<?php

namespace Arara\Process;

use Arara\Process\Exception\InvalidArgumentException;
use Arara\Process\Exception\RuntimeException;

/**
 * Class to handle pidfiles.
 */
class Pidfile
{
    protected $applicationName;
    protected $control;
    protected $fileName;
    protected $fileResource;
    protected $lockDirectory;
    protected $processId;

    /**
     * @param  Control $control Object used to control process information
     * @param  string $applicationName The application name, used as pidfile basename
     * @param  string $lockDirectory Directory were pidfile is stored
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
     * @throws InvalidArgumentException When application name is not valid
     * @param  string $applicationName The application name, used as pidfile basename
     * @return void
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
     * @throws InvalidArgumentException When lock directory is not valid
     * @param  string $lockDirectory Directory were pidfile is stored
     * @return void
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
            $this->fileName = $this->lockDirectory . '/' . $this->applicationName . '.pid';
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
     * @return bool
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
     * @return int|null
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
     * @return void
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

        if (! @fwrite($handle, $this->control->info()->getId() . PHP_EOL)) {
            throw new RuntimeException('Could not write on pidfile');
        }
    }

    /**
     * Finalizes pidfile.
     *
     * Unlock pidfile and removes it.
     *
     * @return void
     */
    public function finalize()
    {
        @flock($this->getFileResource(), LOCK_UN);
        @fclose($this->getFileResource());
        @unlink($this->getFileName());
    }
}
