# Arara\Process
[![Build Status](https://img.shields.io/travis/Arara/Process/master.svg?style=flat-square)](http://travis-ci.org/Arara/Process)
[![Code Quality](https://img.shields.io/scrutinizer/g/Arara/Process/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Arara/Process/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/Arara/Process/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/Arara/Process/?branch=master)
[![Latest Version](https://img.shields.io/packagist/v/arara/process.svg?style=flat-square)](https://packagist.org/packages/arara/process)
[![Total Downloads](https://img.shields.io/packagist/dt/arara/process.svg?style=flat-square)](https://packagist.org/packages/arara/process)
[![License](https://img.shields.io/packagist/l/arara/process.svg?style=flat-square)](https://packagist.org/packages/arara/process)

This library provides a better API to work with processes on Unix-like systems using PHP.

## Installation

The package is available on [Packagist](https://packagist.org/packages/arara/process). You can install it using
[Composer](http://getcomposer.org).

```bash
composer require arara/process
```

### Dependencies

- PHP 5.4+
- [PCNTL](http://php.net/pcntl)
- [POSIX](http://php.net/posix)
- [PHPFluent\Callback](https://github.com/PHPFluent/Callback) (installed by Composer)

Version [1.6.0](https://github.com/Arara/Process/tree/1.6.0) or less of Arara\Process works on PHP 5.3.

## Usage

Along with this document, there are many usage examples in the [examples/](examples/) directory which may be used for reference.

All examples within this document assume you have the following statement at the beginning of the file:

```php
declare(ticks=1);
```

Without this statement, there is no guarantee PHP will handle signals; this is very important for PCNTL to work properly.
It has been required since version 4.3.0 of PHP, so this is not a request of the library but of the PHP language itself.

If you want to know more about _ticks_, we recommend you read http://php.net/declare#control-structures.declare.ticks.

### Action interface

Forks may be encapsulated using `Arara\Process\Action\Action` interface.
All classes that implements this interface must implement two methods:

- `execute(..)`: may contain the action performed in the background
- `trigger(...)`: may contain specific actions to events

Using this interface you can create your own actions and run them in the background.

### Events

The `Arara\Process\Action\Action::trigger(..)` method, as it is written, associates specific actions with events.
Those events can be:

- `Action::EVENT_INIT`: triggered when action is initialized
    - When the action is attached to a Child object
- `Action::EVENT_FORK`: triggered when action is forked
    - After the action is forked it is triggered on the **parent** process
- `Action::EVENT_START`: triggered before the execute() method is executed
- `Action::EVENT_SUCCESS`: triggered when the action is finished with success, that is:
    - When the action does not encounter a PHP error
    - When the action does not throw an exception
    - When the action does not return any value
    - When the action returns an `Action::EVENT_SUCCESS` value
- `Action::EVENT_ERROR`: triggered when the action is encounters an error, that is:
    - When the action encounters a PHP error
    - When the action returns an `Action:EVENT_ERROR` value
- `Action::EVENT_FAILURE`: triggered after the action has finished and failed, that is:
    - When the action throws an exception
    - When the action returns an `Action::EVENT_FAILURE` value
- `Action::EVENT_TIMEOUT`: triggered when the action experiences a timeout
- `Action::EVENT_FINISH `: triggered after the execute() method has executed.

### Callback action

In order to make it easy to execute forks with no need to create a specific class to execute something in the background, there
is a generic implementation that allows a callback to be run in the background; the only thing one must do is pass the callback
to the constructor of this class.

```php
use Arara\Process\Action\Callback;

$callback = new Callback(function () {
    echo "This will be executed in the background!" . PHP_EOL;
});
```

The Callback action provides a way to bind callbacks to be triggered by specific events:

```php
$callback->bind(Callback::EVENT_SUCCESS, function () {
    echo "This will be executed if the action callback was successful!" . PHP_EOL;
});
```

Also, one can bind a callback to multiple events:

```php
$callback->bind(Callback::EVENT_ERROR | Callback::EVENT_FAILURE, function () {
    echo "It is going to be executed if the action fails or get an error" . PHP_EOL;
});
```

### Command action

You may want to run just a Linux command, for that reason there is Command action.
```php
$command = new Command('whoami');
```

Using Command action you can define arguments as second param:
```php
$command = new Command('cp', array('/path/to/source', '/path/to/destination'));
```

If you prefer arguments can be defined by a key => value array:
```php
$command = new Command(
    'find',
    array(
        '/path/to/dir',
        '-name' => '*',
        '-type' => 'f',
    )
);
```

Command action is based on Callback action so you can also bind triggers for events.

### Daemon action

You can create daemons using the `Arara\Process\Action\Daemon` class:

```php
$daemon = new Daemon(
    function (Control $control, Context $context, Daemon $daemon) {
        while (! $daemon->isDying()) {
            // Do whatever you want =)
        }
    }
);
```

This action will:

1. Detach process session from the parent
2. Update process umask
3. Update process work directory
4. Define process GID (if defined)
5. Define process UID (if defined)
6. Recreate standards file descriptors (STDIN, STDOUT and STDERR)
7. Create Pidfile
8. Run the defined payload callback

Daemon action is based on Callback action thus you can also bind triggers for events.

#### Daemon options

Daemon action class has some options that allows you to change some behaviours:

- `name`: Name used by pidfile (default _arara_)
- `lock_dir`: Lock directory for pidfile (default _/var_/run)
- `work_dir`: Work directory (default _/_)
- `umask`: Default umask value (default _0_)
- `user_id`: When defined changed the daemon UID (default _NULL_)
- `group_id`: When defined changed the daemon GID (default _NULL_)
- `stdin`: File to use as `STDIN` (default _/dev/null_)
- `stdout`: File to use as `STDOUT` (default _/dev/null_)
- `stderr`: File to use as `STDERR` (default _/dev/null_)

You can change default daemon options by defining it on class constructor:
```php
$daemon = new Daemon(
    $callback,
    array(
        'name' => 'mydaemonname',
        'lock_dir' => __DIR__,
    )
);
```

After the object is created you may change all options:
```php
$daemon->setOptions(
    array(
        'stdout' => '/tmp/daemon.stdout',
        'stderr' => '/tmp/daemon.stderr',
    )
);
```

Also you can change just an option:
```php
$daemon->setOption('work_dir', __DIR__);
```

### Starting a process in the background

The class `Arara\Process\Child` allows you to execute any action in the background.

```php
$child = new Child(
    new Daemon(function () {
        // What my daemon does...
    }),
    new Control()
);
$child->start(); // Runs the callback in the background
```

The above example runs the Daemon action in the background, but one can use any class which implements
the `Arara\Process\Action\Action` interface like Callback action.

### Check if the process is running

Checking to see if a process is running is a very common routine; to perform this using this library you may call:

```php
$child->isRunning(); // Returns TRUE if it is running or FALSE if it is not
```

This method not only checks the state of the object, but also checks to see if the process is already running on the system.

### Terminating the process

If the process has already started, this tells the process to terminate, but does not force it.

```php
$child->terminate(); // Sends a SIGTERM to the process
```

### Killing the process

If it has already started, this forces the process to terminate immediately.

```php
$child->kill(); // Sends a SIGKILL to the process
```

### Waiting on the process

If you want to wait on the process to finish, instead of just starting the process in the background, you can call:

```php
$child->wait();
```

The next line of code will be executed after the process finishes.

### Getting a process' status

It is possible to get the status of a process after waiting for it finish.
The `Arara\Process\Child` class has a method `getStatus()` which allows you to check the status of a process.

```php
$child->getStatus(); // Returns an Arara\Process\Control\Status instance
```

Internally, this calls the `wait()` method, in order to wait for the process to finish - and then get its status.

#### Get the exit code of the process

```php
$child->getStatus()->getExitStatus();
```

#### Get the signal which caused the process to stop

```php
$child->getStatus()->getStopSignal();
```

#### Get the signal which caused the process to terminate

```php
$child->getStatus()->getTerminateSignal();
```

#### Checks if the status code represents a normal exit

```php
$child->getStatus()->isExited();
```

#### Checks whether the status code represents a termination due to a signal

```php
$child->getStatus()->isSignaled();
```

#### Checks whether the process is stopped

```php
$child->getStatus()->isStopped();
```

#### Checks if the process was finished successfully

```php
$child->getStatus()->isSuccessful();
```

### Spawning

Since you are working with forks you are able work with spawn as well. The `Arara\Process\Pool` class provides a simple
way to work with it.

This class handles the queue of process dynamically, the only thing you have to do is provide the limit of children you
want in the constructor and then attach the children.

```php
$maxConcurrentChildren = 2;
$pool = new Pool($maxConcurrentChildren);
$pool->start();

$pool->attach(new Child(/* ... */));
$pool->attach(new Child(/* ... */));
$pool->attach(new Child(/* ... */));
$pool->attach(new Child(/* ... */));
// ...
```

The number of children it has does not matter; it will only run 2 process simultaneously; when one of those process is
finished, it is removed from the queue and a new slot is opened.

The `Arara\Process\Pool` class contains most of the methods of `Arara\Process\Child` class:

- `isRunning()`
- `kill()`
- `start()`
- `terminate()`
- `wait()`

This behaves similarly for all methods.

### Control class

You can also handle processes without using Pool, Child or the Action classes.

We provide a simple API to work with the `pcntl_*` and `posix_*` functions. You can learn more by reading the
code of `Arara\Process\Control` and its dependencies, but here is an example:

```php
$control = new Control();
$pid = $control->fork();// Throws RuntimeException when pcntl_fork() returns -1
if ($pid > 0) {
    echo 'Waiting on child...' . PHP_EOL;
    $control->waitProcessId($pid);
    echo 'Child finished' . PHP_EOL;
    $control->quit();
}

echo 'Child process has PID ' . $control->info()->getId() . PHP_EOL;
echo 'Child process has parent PID ' . $control->info()->getParentId() . PHP_EOL;

$control->flush(2.5); // Will try to flush current process memory and sleep by 2 and a half seconds
$control->signal()->send('kill'); // Will send SIGKILL to the current process (the child)
```

### Pidfile class

If you are working with background tasks you may want to create a lock to avoid people running your script twice. For this
purpose there is the class `Arara\Process\Pidfile`.

```php
$control = new Control();
$applicationName = 'my_app';
$pidfile = new Pidfile($control, $applicationName);
$pidfile->initialize();

// Whatever you need here...

$pidfile->finalize();
```

The second time someone runs it an exception is thrown. We recommend you put this code into a `try..catch` statement.
