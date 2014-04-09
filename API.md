# Arara\Process

## API

### Arara/Process/Action/Action
- EVENT_START
- EVENT_SUCCESS
- EVENT_ERROR
- EVENT_FAILURE
- EVENT_TIMEOUT
- EVENT_FINISH
- execute(Arara/Process/Control $control) : int
- trigger(int $event, Arara/Process/Control $control, array $context) : void

### Arara/Process/Action/Callback implements Arara/Process/Action/Action
- __construct(callable $callback)
- bind(int $event, callable $callback) : void

### Arara/Process/Process
- hasId() : bool
- getId() : int
- isRunning() : bool
- getStatus() : Arara/Process/Control/Status
- kill() : void
- start() : void
- terminate() : void
- wait() : void

### Arara/Process/Pool implements Process, Countable, IteratorAggregate
- __construct(int $childrenLimit)
- attach(Process $process)
- detach(Process $process)
- shift() : Process

### Arara/Process/Child implements Process
- __construct(Arara/Process/Action/Action $action, Arara/Process/Control $control, int $timeout = 0)

### Arara/Process/Control
- execute(string $path, array $args = array(), array $envs = array()) : void
- fork() : int
- info() : Arara/Process/Control/Info
- quit(int $code) : void
- signal() : Arara/Process/Control/Signal
- wait(int &$status = null, int $options = 0) : int
- waitProcessId(int $processId, int &$status = null, int $options = 0) : int

### Arara/Process/Control/Info
- getId() : int
- getParentId() : int
- getUserId() : int
- setUserId(int $userId) : void
- getUser() : string
- setUser(string $login) : void
- getGroupId() : int
- setGroupId(int $groupId) : void
- getGroup() : string
- setGroup(string $group) : void
- detachSession() : bool
- getSessionId() : int

### Arara/Process/Control/Status
- __construct(int $status)
- getExitStatus() : int
- getStopSignal() : int
- getTerminateSignal() : int
- isExited() : bool
- isSignaled() : bool
- isStopped() : bool
- isSuccessful() : bool

### Arara/Process/Control/Signal
- alarm(int $seconds) : int
- dispatch() : bool
- handle(int | string $signal, callable $callback) : bool
- ignore(int | string $signal) : bool
- send(int | string $signal, int $pid) : bool

### Arara/Process/Control/Signal/AbstractHandler
- __construct(Arara/Process/Control $control)
- __invoke($signal)

### Arara/Process/Control/Signal/ChildHandler extends Arara/Process/Control/Signal/Handlers/AbstractHandler
### Arara/Process/Control/Signal/InterruptHandler extends Arara/Process/Control/Signal/Handlers/AbstractHandler
### Arara/Process/Control/Signal/QuitHandler extends Arara/Process/Control/Signal/Handlers/AbstractHandler
### Arara/Process/Control/Signal/TerminateHandler extends Arara/Process/Control/Signal/Handlers/AbstractHandler
