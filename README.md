# Arara\Process
[![Build Status](https://secure.travis-ci.org/Arara/Process.png)](http://travis-ci.org/Arara/Process)
[![Code quality](https://scrutinizer-ci.com/g/Arara/Process/badges/quality-score.png?s=1ff1707a244411f92bd01de4f736c44d5e8d19f0)](https://scrutinizer-ci.com/g/Arara/Process/)
[![Code coverage](https://scrutinizer-ci.com/g/Arara/Process/badges/coverage.png?s=71d920b6fa23b85fa0b50e331c8efccf9cb28ebf)](https://scrutinizer-ci.com/g/Arara/Process/)
[![Total Downloads](https://poser.pugx.org/arara/process/downloads.png)](https://packagist.org/packages/arara/process)
[![License](https://poser.pugx.org/arara/process/license.png)](https://packagist.org/packages/arara/process)
[![Latest Stable Version](https://poser.pugx.org/arara/process/v/stable.png)](https://packagist.org/packages/arara/process)
[![Latest Unstable Version](https://poser.pugx.org/arara/process/v/unstable.png)](https://packagist.org/packages/arara/process)

This library provides a better API to work with processes on Unix-like systems using PHP.

## Installation

The package is available on [Packagist](https://packagist.org/packages/arara/process). You can install it using
[Composer](http://getcomposer.org).

```bash
composer require arara/process
```

### Dependencies

- PHP 5.3+
- [PCNTL](http://php.net/pcntl)
- [POSIX](http://php.net/posix)

## Usage

Along with this document, there are many usage examples in the "examples/" directory which may be used for reference.

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

$action = new Callback(function () {
    echo "This will be executed in the background!" . PHP_EOL;
});
```

The Callback action provides a way to bind callbacks to be triggered by specific events:

```php
$action->bind(Callback::EVENT_SUCCESS, function () {
    echo "This will be executed if the action callback was successful!" . PHP_EOL;
});
```

Also, one can bind a callback to multiple events:

```php
$action->bind(Callback::EVENT_ERROR | Callback::EVENT_FAILURE, function () {
    echo "It is going to be executed if the action fails or get an error" . PHP_EOL;
});
```

### Starting a process in the background

The class `Arara\Process\Child` allows you to execute any action in the background.

```php
$child = new Child(
    new Callback(function (Control $control) {
        echo 'PID ' . $control->info()->getId() . ' is running in the background' . PHP_EOL;
    }),
    new Control()
);
$child->start(); // Runs the callback in the background
```

The above example runs the Callback action in the background, but one can use any class which implements
the `Arara\Process\Action\Action` interface.

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
