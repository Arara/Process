# Arara\Process
[![Build Status](https://secure.travis-ci.org/Arara/Process.png)](http://travis-ci.org/Arara/Process)
[![Code quality](https://scrutinizer-ci.com/g/Arara/Process/badges/quality-score.png?s=1ff1707a244411f92bd01de4f736c44d5e8d19f0)](https://scrutinizer-ci.com/g/Arara/Process/)
[![Code coverage](https://scrutinizer-ci.com/g/Arara/Process/badges/coverage.png?s=71d920b6fa23b85fa0b50e331c8efccf9cb28ebf)](https://scrutinizer-ci.com/g/Arara/Process/)
[![Total Downloads](https://poser.pugx.org/arara/process/downloads.png)](https://packagist.org/packages/arara/process)
[![License](https://poser.pugx.org/arara/process/license.png)](https://packagist.org/packages/arara/process)
[![Latest Stable Version](https://poser.pugx.org/arara/process/v/stable.png)](https://packagist.org/packages/arara/process)
[![Latest Unstable Version](https://poser.pugx.org/arara/process/v/unstable.png)](https://packagist.org/packages/arara/process)

This library provides a better API to work process on Unix-like systems using PHP.

## Installation

Package is available on [Packagist](https://packagist.org/packages/arara/process), you can install it using
[Composer](http://getcomposer.org).

```bash
composer require arara/process
```

### Dependencies

- PHP 5.3+
- [PCNTL](http://php.net/pcntl)
- [POSIX](http://php.net/posix)

## Usage

Besides this document there are a bunch of usage examples in "examples/" directory you can use as reference.

All examples of this document are assuming you have the following statement at the beginning of the file:

```php
declare(ticks=1);
```

Without this statement there is no guarantee PHP can handle signals, so this is very important for PCNTL works properly.
It is required since version 4.3.0 of PHP, so it's not a request of the library but of the language.

If you want to know more about _ticks_ we recommend you read http://php.net/declare#control-structures.declare.ticks.

### Action interface

Forks may be encapsulated using `Arara\Process\Action\Action` interface.
All classes that implements this interface must implement two methods:

- `execute(..)`: may contain the action performed in the background
- `trigger(...)`: may contain specific actions to events

Using this interface you can create your own actions and run them in background.

### Events

The `Arara\Process\Action\Action::trigger(..)` method, as was already written, may contain specific actions to events,
those events can be:

- `Action::EVENT_START`: triggered before the execute() method be executed
- `Action::EVENT_SUCCESS`: triggered when action is finished with success
    - When action does not get a PHP error
    - When action does not throws an exception
    - When action does not return any value
    - When action returns `Action::EVENT_SUCCESS` value
- `Action::EVENT_ERROR`: triggered when action is finished with an error
    - When action get a PHP error
    - When action returns `Action:EVENT_ERROR` value
- `Action::EVENT_FAILURE`: triggered when action is finished with a failure
    - When action throws an exception
    - When action returns `Action::EVENT_FAILURE` value
- `Action::EVENT_TIMEOUT`: triggered when action get a timeout
- `Action::EVENT_FINISH `: triggered after the execute() method be executed.

### Callback action

In order to make easy to execute forks with no need to create an specific class to execute something in background there
is a generic implementation that allows to run a callback in background, the only thing you have to do is to define this
callback on the constructor of this class.

```php
use Arara\Process\Action\Callback;

$action = new Callback(function () {
    echo "It is going to be executed in background!" . PHP_EOL;
});
```

The Callback action provides a way to bind callbacks to be triggered on specific events:

```php
$action->bind(Callback::EVENT_SUCCESS, function () {
    echo "It is going to be executed if the action callback was successful!" . PHP_EOL;
});
```

Also, you can combine events when you're binding:

```php
$action->bind(Callback::EVENT_ERROR | Callback::EVENT_FAILURE, function () {
    echo "It is going to be executed if the action fails or get an error" . PHP_EOL;
});
```

### Starting a process in background

The class `Arara\Process\Child` allows you to execute any action in background.

```php
$child = new Child(
    new Callback(function (Control $control) {
        echo 'PID ' . $control->info()->getId() . ' is running in background' . PHP_EOL;
    }),
    new Control()
);
$child->start(); // Runs the callback in background
```

This example above runs the Callback action in background but you can use any class that implements
`Arara\Process\Action\Action` interface.

### Check if process is running

Check if a process is running is a very common routine, to do it using this library you may call:

```php
$child->isRunning(); // Returns TRUE if is running or FALSE if is not
```

This method not only check the state of the object but also check if the process is already running on the system.

### Terminating the process

If already started, tells the process to terminate but do not forces it.

```php
$child->terminate(); // Sends a SIGTERM to the process
```

### Killing the process

If already started, forces the process to terminate immediately.

```php
$child->kill(); // Sends a SIGKILL to the process
```

### Waiting the process

If you want to wait the process to finish instead of just start the process in background you can call:

```php
$child->wait();
```

The next line of code will just be executed after the process finish.

### Getting process status

It is possible to get the status of a process after wait it finish.
`Arara\Process\Child` class has a method called `getStatus()` that allows you to check the status of a process.

```php
$child->getStatus(); // Returns a Arara\Process\Control\Status instance
```

Internally it calls the `wait()` method in order wait the process finish and then get its status.

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

#### Checks if status code represents a normal exit

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

#### Checks if the process was finished successful

```php
$child->getStatus()->isSuccessful();
```

### Spawning

Since you are working with forks you are able work with spawn as well. The `Arara\Process\Pool` class provides a simple
way to work with it.

This class handles the queue of process dynamically, the only thing you have to do is provide the limit of children you
want on the constructor and then attach the children.

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

Does not matter the number of children it has it will only run 2 process simultaneously, when one of those process is
finished it is removed from the queue and a new slot is opened.

The `Arara\Process\Pool` class contains most methods of `Arara\Process\Child` class:

- `isRunning()`
- `kill()`
- `start()`
- `terminate()`
- `wait()`

It's pretty much the same behaviour for all methods.

### Control class

You can also handle processes without using Pool, Child or the Action classes.

We provide a simple API to work with the `pcntl_*` and `posix_*` functions, you can get more information reading the
code of `Arara\Process\Control` and its dependencies but here is an example of how to use it.

```php
$control = new Control();
$pid = $control->fork();// Throws RuntimeException when pcntl_fork() returns -1
if ($pid > 0) {
    echo 'Waiting child...' . PHP_EOL;
    $control->waitProcessId($pid);
    echo 'Child finished' . PHP_EOL;
    $control->quit();
}

echo 'Child process has PID ' . $control->info()->getId() . PHP_EOL;
echo 'Child process has parent PID ' . $control->info()->getParentId() . PHP_EOL;

$control->signal()->send('kill'); // Will send SIGKILL to the current process (the child)
```

### Pidfile class

If you are working with background tasks you may want to create a lock to avoid people run your script twice, for that
reason there is the class `Arara\Process\Pidfile`.

```php
$control = new Control();
$applicationName = 'my_app';
$pidfile = new Pidfile($control, $applicationName);
$pidfile->initialize();

// Whatever you need here...

$pidfile->finalize();
```

The second time someone runs it an exception is throwed. We recommend you put your code into a `try..catch` statement.
