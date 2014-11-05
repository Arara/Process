<?php

namespace Arara\Process;

use InvalidArgumentException;
use RuntimeException;

class Pidfile
{
    protected $applicationName;
    protected $control;
    protected $fileName;
    protected $fileResource;
    protected $lockDirectory;

    public function __construct(Control $control, $applicationName = 'arara', $lockDirectory = '/var/run')
    {
        $this->control = $control;
        $this->setApplicationName($applicationName);
        $this->setLockDirectory($lockDirectory);
    }

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

    protected function getFileName()
    {
        if (null === $this->fileName) {
            $this->fileName = $this->lockDirectory . '/' . $this->applicationName . '.pid';
        }

        return $this->fileName;
    }

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

    public function isActive()
    {
        $pid = $this->getPid();
        if (null === $pid) {
            return false;
        }

        return $this->control->signal()->send(0, $pid);
    }

    public function getPid()
    {
        $content = fgets($this->getFileResource());
        $pieces = explode(PHP_EOL, trim($content));

        return reset($pieces) ?: null;
    }

    public function initialize()
    {
        if ($this->isActive()) {
            throw new RuntimeException('Process is already active');
        }

        if (! @flock($this->getFileResource(), (LOCK_EX | LOCK_NB))) {
            throw new RuntimeException('Could not lock pidfile');
        }

        if (! @fwrite($this->getFileResource(), $this->control->info()->getId() . PHP_EOL)) {
            throw new RuntimeException('Could not write on pidfile');
        }
    }

    public function finalize()
    {
        @flock($this->getFileResource(), LOCK_UN);
        @fclose($this->getFileResource());
        @unlink($this->getFileName());
    }
}
