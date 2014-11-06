<?php

namespace Arara\Process;

function pcntl_fork()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_fork'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_fork']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_fork']['return'])) {
        $return = $GLOBALS['arara']['pcntl_fork']['return'];
    }
    $GLOBALS['arara']['pcntl_fork']['count'] = $count;

    return $return;
}

function pcntl_waitpid($pid, &$status = null, $options = 0)
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_waitpid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    if (isset($GLOBALS['arara']['pcntl_waitpid']['status'])) {
        $status = $GLOBALS['arara']['pcntl_waitpid']['status'];
    }

    $GLOBALS['arara']['pcntl_waitpid']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_waitpid']['return'])) {
        $return = $GLOBALS['arara']['pcntl_waitpid']['return'];
    }
    $GLOBALS['arara']['pcntl_waitpid']['count'] = $count;

    return $return;
}

function pcntl_wait(&$status = null, $options = 0)
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wait'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    if (isset($GLOBALS['arara']['pcntl_wait']['status'])) {
        $status = $GLOBALS['arara']['pcntl_wait']['status'];
    }

    $GLOBALS['arara']['pcntl_wait']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wait']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wait']['return'];
    }
    $GLOBALS['arara']['pcntl_wait']['count'] = $count;

    return $return;
}

function pcntl_signal()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_signal'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = true;

    $GLOBALS['arara']['pcntl_signal']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_signal']['return'])) {
        $return = $GLOBALS['arara']['pcntl_signal']['return'];
    }
    $GLOBALS['arara']['pcntl_signal']['count'] = $count;

    return $return;
}

function pcntl_signal_dispatch()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_signal_dispatch'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_signal_dispatch']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_signal_dispatch']['return'])) {
        $return = $GLOBALS['arara']['pcntl_signal_dispatch']['return'];
    }
    $GLOBALS['arara']['pcntl_signal_dispatch']['count'] = $count;

    return $return;
}

function pcntl_wifexited()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wifexited'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_wifexited']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wifexited']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wifexited']['return'];
    }
    $GLOBALS['arara']['pcntl_wifexited']['count'] = $count;

    return $return;
}

function pcntl_wifstopped()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wifstopped'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_wifstopped']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wifstopped']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wifstopped']['return'];
    }
    $GLOBALS['arara']['pcntl_wifstopped']['count'] = $count;

    return $return;
}

function pcntl_wifsignaled()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wifsignaled'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_wifsignaled']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wifsignaled']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wifsignaled']['return'];
    }
    $GLOBALS['arara']['pcntl_wifsignaled']['count'] = $count;

    return $return;
}

function pcntl_wexitstatus()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wexitstatus'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_wexitstatus']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wexitstatus']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wexitstatus']['return'];
    }
    $GLOBALS['arara']['pcntl_wexitstatus']['count'] = $count;

    return $return;
}

function pcntl_wtermsig()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wtermsig'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_wtermsig']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wtermsig']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wtermsig']['return'];
    }
    $GLOBALS['arara']['pcntl_wtermsig']['count'] = $count;

    return $return;
}

function pcntl_wstopsig()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_wstopsig'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_wstopsig']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_wstopsig']['return'])) {
        $return = $GLOBALS['arara']['pcntl_wstopsig']['return'];
    }
    $GLOBALS['arara']['pcntl_wstopsig']['count'] = $count;

    return $return;
}

function pcntl_exec()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_exec'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_exec']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_exec']['return'])) {
        $return = $GLOBALS['arara']['pcntl_exec']['return'];
    }
    $GLOBALS['arara']['pcntl_exec']['count'] = $count;

    return $return;
}

function pcntl_alarm()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_alarm'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_alarm']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_alarm']['return'])) {
        $return = $GLOBALS['arara']['pcntl_alarm']['return'];
    }
    $GLOBALS['arara']['pcntl_alarm']['count'] = $count;

    return $return;
}

function pcntl_get_last_error()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_get_last_error'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_get_last_error']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_get_last_error']['return'])) {
        $return = $GLOBALS['arara']['pcntl_get_last_error']['return'];
    }
    $GLOBALS['arara']['pcntl_get_last_error']['count'] = $count;

    return $return;
}

function pcntl_errno()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_errno'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_errno']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_errno']['return'])) {
        $return = $GLOBALS['arara']['pcntl_errno']['return'];
    }
    $GLOBALS['arara']['pcntl_errno']['count'] = $count;

    return $return;
}

function pcntl_strerror()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_strerror'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_strerror']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_strerror']['return'])) {
        $return = $GLOBALS['arara']['pcntl_strerror']['return'];
    }
    $GLOBALS['arara']['pcntl_strerror']['count'] = $count;

    return $return;
}

function pcntl_getpriority()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_getpriority'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_getpriority']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_getpriority']['return'])) {
        $return = $GLOBALS['arara']['pcntl_getpriority']['return'];
    }
    $GLOBALS['arara']['pcntl_getpriority']['count'] = $count;

    return $return;
}

function pcntl_setpriority()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_setpriority'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_setpriority']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_setpriority']['return'])) {
        $return = $GLOBALS['arara']['pcntl_setpriority']['return'];
    }
    $GLOBALS['arara']['pcntl_setpriority']['count'] = $count;

    return $return;
}

function pcntl_sigprocmask()
{
    static $count;
    if (! isset($GLOBALS['arara']['pcntl_sigprocmask'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['pcntl_sigprocmask']['args'] = $args;
    if (isset($GLOBALS['arara']['pcntl_sigprocmask']['return'])) {
        $return = $GLOBALS['arara']['pcntl_sigprocmask']['return'];
    }
    $GLOBALS['arara']['pcntl_sigprocmask']['count'] = $count;

    return $return;
}

function posix_kill()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_kill'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_kill']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_kill']['return'])) {
        $return = $GLOBALS['arara']['posix_kill']['return'];
    }
    $GLOBALS['arara']['posix_kill']['count'] = $count;

    return $return;
}

function posix_getpid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getpid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getpid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getpid']['return'])) {
        $return = $GLOBALS['arara']['posix_getpid']['return'];
    }
    $GLOBALS['arara']['posix_getpid']['count'] = $count;

    return $return;
}

function posix_getppid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getppid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getppid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getppid']['return'])) {
        $return = $GLOBALS['arara']['posix_getppid']['return'];
    }
    $GLOBALS['arara']['posix_getppid']['count'] = $count;

    return $return;
}

function posix_getuid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getuid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getuid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getuid']['return'])) {
        $return = $GLOBALS['arara']['posix_getuid']['return'];
    }
    $GLOBALS['arara']['posix_getuid']['count'] = $count;

    return $return;
}

function posix_setuid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_setuid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_setuid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_setuid']['return'])) {
        $return = $GLOBALS['arara']['posix_setuid']['return'];
    }
    $GLOBALS['arara']['posix_setuid']['count'] = $count;

    return $return;
}

function posix_geteuid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_geteuid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_geteuid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_geteuid']['return'])) {
        $return = $GLOBALS['arara']['posix_geteuid']['return'];
    }
    $GLOBALS['arara']['posix_geteuid']['count'] = $count;

    return $return;
}

function posix_seteuid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_seteuid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_seteuid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_seteuid']['return'])) {
        $return = $GLOBALS['arara']['posix_seteuid']['return'];
    }
    $GLOBALS['arara']['posix_seteuid']['count'] = $count;

    return $return;
}

function posix_getgid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getgid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getgid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getgid']['return'])) {
        $return = $GLOBALS['arara']['posix_getgid']['return'];
    }
    $GLOBALS['arara']['posix_getgid']['count'] = $count;

    return $return;
}

function posix_setgid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_setgid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_setgid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_setgid']['return'])) {
        $return = $GLOBALS['arara']['posix_setgid']['return'];
    }
    $GLOBALS['arara']['posix_setgid']['count'] = $count;

    return $return;
}

function posix_getegid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getegid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getegid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getegid']['return'])) {
        $return = $GLOBALS['arara']['posix_getegid']['return'];
    }
    $GLOBALS['arara']['posix_getegid']['count'] = $count;

    return $return;
}

function posix_setegid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_setegid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_setegid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_setegid']['return'])) {
        $return = $GLOBALS['arara']['posix_setegid']['return'];
    }
    $GLOBALS['arara']['posix_setegid']['count'] = $count;

    return $return;
}

function posix_getgroups()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getgroups'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getgroups']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getgroups']['return'])) {
        $return = $GLOBALS['arara']['posix_getgroups']['return'];
    }
    $GLOBALS['arara']['posix_getgroups']['count'] = $count;

    return $return;
}

function posix_getlogin()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getlogin'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getlogin']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getlogin']['return'])) {
        $return = $GLOBALS['arara']['posix_getlogin']['return'];
    }
    $GLOBALS['arara']['posix_getlogin']['count'] = $count;

    return $return;
}

function posix_getpgrp()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getpgrp'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getpgrp']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getpgrp']['return'])) {
        $return = $GLOBALS['arara']['posix_getpgrp']['return'];
    }
    $GLOBALS['arara']['posix_getpgrp']['count'] = $count;

    return $return;
}

function posix_setsid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_setsid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_setsid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_setsid']['return'])) {
        $return = $GLOBALS['arara']['posix_setsid']['return'];
    }
    $GLOBALS['arara']['posix_setsid']['count'] = $count;

    return $return;
}

function posix_setpgid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_setpgid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_setpgid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_setpgid']['return'])) {
        $return = $GLOBALS['arara']['posix_setpgid']['return'];
    }
    $GLOBALS['arara']['posix_setpgid']['count'] = $count;

    return $return;
}

function posix_getpgid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getpgid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getpgid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getpgid']['return'])) {
        $return = $GLOBALS['arara']['posix_getpgid']['return'];
    }
    $GLOBALS['arara']['posix_getpgid']['count'] = $count;

    return $return;
}

function posix_getsid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getsid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getsid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getsid']['return'])) {
        $return = $GLOBALS['arara']['posix_getsid']['return'];
    }
    $GLOBALS['arara']['posix_getsid']['count'] = $count;

    return $return;
}

function posix_uname()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_uname'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_uname']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_uname']['return'])) {
        $return = $GLOBALS['arara']['posix_uname']['return'];
    }
    $GLOBALS['arara']['posix_uname']['count'] = $count;

    return $return;
}

function posix_times()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_times'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_times']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_times']['return'])) {
        $return = $GLOBALS['arara']['posix_times']['return'];
    }
    $GLOBALS['arara']['posix_times']['count'] = $count;

    return $return;
}

function posix_ctermid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_ctermid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_ctermid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_ctermid']['return'])) {
        $return = $GLOBALS['arara']['posix_ctermid']['return'];
    }
    $GLOBALS['arara']['posix_ctermid']['count'] = $count;

    return $return;
}

function posix_ttyname()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_ttyname'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_ttyname']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_ttyname']['return'])) {
        $return = $GLOBALS['arara']['posix_ttyname']['return'];
    }
    $GLOBALS['arara']['posix_ttyname']['count'] = $count;

    return $return;
}

function posix_isatty()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_isatty'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_isatty']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_isatty']['return'])) {
        $return = $GLOBALS['arara']['posix_isatty']['return'];
    }
    $GLOBALS['arara']['posix_isatty']['count'] = $count;

    return $return;
}

function posix_getcwd()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getcwd'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getcwd']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getcwd']['return'])) {
        $return = $GLOBALS['arara']['posix_getcwd']['return'];
    }
    $GLOBALS['arara']['posix_getcwd']['count'] = $count;

    return $return;
}

function posix_mkfifo()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_mkfifo'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_mkfifo']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_mkfifo']['return'])) {
        $return = $GLOBALS['arara']['posix_mkfifo']['return'];
    }
    $GLOBALS['arara']['posix_mkfifo']['count'] = $count;

    return $return;
}

function posix_mknod()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_mknod'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_mknod']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_mknod']['return'])) {
        $return = $GLOBALS['arara']['posix_mknod']['return'];
    }
    $GLOBALS['arara']['posix_mknod']['count'] = $count;

    return $return;
}

function posix_access()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_access'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_access']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_access']['return'])) {
        $return = $GLOBALS['arara']['posix_access']['return'];
    }
    $GLOBALS['arara']['posix_access']['count'] = $count;

    return $return;
}

function posix_getgrnam()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getgrnam'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getgrnam']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getgrnam']['return'])) {
        $return = $GLOBALS['arara']['posix_getgrnam']['return'];
    }
    $GLOBALS['arara']['posix_getgrnam']['count'] = $count;

    return $return;
}

function posix_getgrgid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getgrgid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getgrgid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getgrgid']['return'])) {
        $return = $GLOBALS['arara']['posix_getgrgid']['return'];
    }
    $GLOBALS['arara']['posix_getgrgid']['count'] = $count;

    return $return;
}

function posix_getpwnam()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getpwnam'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getpwnam']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getpwnam']['return'])) {
        $return = $GLOBALS['arara']['posix_getpwnam']['return'];
    }
    $GLOBALS['arara']['posix_getpwnam']['count'] = $count;

    return $return;
}

function posix_getpwuid()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getpwuid'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getpwuid']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getpwuid']['return'])) {
        $return = $GLOBALS['arara']['posix_getpwuid']['return'];
    }
    $GLOBALS['arara']['posix_getpwuid']['count'] = $count;

    return $return;
}

function posix_getrlimit()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_getrlimit'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_getrlimit']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_getrlimit']['return'])) {
        $return = $GLOBALS['arara']['posix_getrlimit']['return'];
    }
    $GLOBALS['arara']['posix_getrlimit']['count'] = $count;

    return $return;
}

function posix_get_last_error()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_get_last_error'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_get_last_error']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_get_last_error']['return'])) {
        $return = $GLOBALS['arara']['posix_get_last_error']['return'];
    }
    $GLOBALS['arara']['posix_get_last_error']['count'] = $count;

    return $return;
}

function posix_errno()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_errno'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_errno']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_errno']['return'])) {
        $return = $GLOBALS['arara']['posix_errno']['return'];
    }
    $GLOBALS['arara']['posix_errno']['count'] = $count;

    return $return;
}

function posix_strerror()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_strerror'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_strerror']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_strerror']['return'])) {
        $return = $GLOBALS['arara']['posix_strerror']['return'];
    }
    $GLOBALS['arara']['posix_strerror']['count'] = $count;

    return $return;
}

function posix_initgroups()
{
    static $count;
    if (! isset($GLOBALS['arara']['posix_initgroups'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['posix_initgroups']['args'] = $args;
    if (isset($GLOBALS['arara']['posix_initgroups']['return'])) {
        $return = $GLOBALS['arara']['posix_initgroups']['return'];
    }
    $GLOBALS['arara']['posix_initgroups']['count'] = $count;

    return $return;
}
