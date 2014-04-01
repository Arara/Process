<?php

namespace Arara\Process;

use Arara\Process\Ipc\Ipc;
use ErrorException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use UnderflowException;

class Process
{
    const STATUS_SUCESS = 2;
    const STATUS_ERROR = 4;
    const STATUS_FAIL = 8;

    private $action;
    private $ipc;
    private $userId;
    private $groupId;
    private $pid;
    private $callbacks = array();

    public function __construct($action, Ipc $ipc, $userId = null, $groupId = null)
    {
        if (! is_callable($action)) {
            throw new InvalidArgumentException('Action must be a valid callback');
        }

        if (null !== $userId && ! posix_getpwuid($userId)) {
            throw new InvalidArgumentException(sprintf('The given UID "%s" is not valid', $userId));
        }

        if (null !== $groupId && ! posix_getgrgid($groupId)) {
            throw new InvalidArgumentException(sprintf('The given GID "%s" is not valid', $groupId));
        }

        $this->action = $action;
        $this->ipc = $ipc;
        $this->userId = $userId ?: posix_getuid();
        $this->groupId = $groupId ?: posix_getgid();
    }

    public function setCallback($callback, $status)
    {
        if (! is_callable($callback)) {
            $message = 'Callback given is not a valid callable';
            throw new InvalidArgumentException($message);
        }

        $this->callbacks[$status] = $callback;

        return $this;
    }

    public function getCallback($status)
    {
        $returnCallback = function () {};
        foreach ($this->callbacks as $key => $callback) {
            if ($status !== ($key & $status)) {
                continue;
            }
            $returnCallback = $callback;
            break;
        }

        return $returnCallback;
    }

    public function start(Signal $signal)
    {
        if (true === $this->hasPid()) {
            throw new UnderflowException('Process already started');
        }

        $pid = @pcntl_fork();
        if ($pid === -1) {
            return false;
        }

        if ($pid > 0) {
            $this->pid = $pid;

            return true;
        }

        set_error_handler(
            function ($severity, $message, $filename, $line) {
                throw new ErrorException($message, 0, $severity, $filename, $line);
            },
            E_ALL & ~E_NOTICE
        );

        ob_start();

        try {
            posix_setgid($this->getGroupId());
            posix_setuid($this->getUserId());

            $userId = posix_getuid();
            $groupId = posix_getgid();
            if ($userId != $this->getUserId() || $groupId != $this->getGroupId()) {
                $format = 'Unable to fork process as "%d:%d". "%d:%d" given';
                $message = sprintf($format, $this->getUserId(), $this->getGroupId(), $userId, $groupId);
                throw new RuntimeException($message);
            }

            $result = call_user_func($this->action, $this->getIpc());
            $status = self::STATUS_SUCESS;
            $exitCode = 0;
        } catch (ErrorException $exception) {
            $result = $exception;
            $status = self::STATUS_FAIL;
            $exitCode = 1;
        } catch (Exception $exception) {
            $result = $exception;
            $status = self::STATUS_ERROR;
            $exitCode = 2;
        }

        $this->getIpc()->save('result', $result);
        $this->getIpc()->save('status', $status);
        $this->getIpc()->save('output', ob_get_clean());

        try {
            call_user_func($this->getCallback($status), $this->getIpc(), $result);
        } catch (Exception $exception) {
            // Pokémon Exception Handling
        }

        restore_error_handler();

        $signal->quit($exitCode);
    }

    public function stop()
    {
        $return = posix_kill($this->getPid(), SIGTERM);

        return $return;
    }

    public function kill()
    {
        $return = posix_kill($this->getPid(), SIGKILL);

        return $return;
    }

    public function wait()
    {
        if (! $this->hasPid()) {
            return true;
        }

        pcntl_waitpid($this->getPid(), $status);

        return $status;
    }

    public function isRunning()
    {
        return ($this->hasPid() && posix_kill($this->getPid(), 0));
    }

    public function hasPid()
    {
        return (null !== $this->pid);
    }

    public function getPid()
    {
        if (false === $this->hasPid()) {
            $message = 'There is not defined process';
            throw new UnderflowException($message);
        }

        return $this->pid;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }

    public function getIpc()
    {
        return $this->ipc;
    }

    public function getResult()
    {
        return $this->getIpc()->load('result');
    }

    public function getOutput()
    {
        return $this->getIpc()->load('output');
    }

    public function getStatus()
    {
        return $this->getIpc()->load('status');
    }

    public function isSuccessful()
    {
        return ($this->getIpc()->load('status') == self::STATUS_SUCESS);
    }

    public function setPriority($priority)
    {
        if (false === pcntl_setpriority($priority, $this->getPid(), PRIO_PROCESS)) {
            $message = 'Unable to set the priority';
            throw new RuntimeException($message);
        }

        return $this;
    }
}
