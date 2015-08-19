<?php

/*
 * This file is part of the Arara\Process package.
 *
 * Copyright (c) Henrique Moody <henriquemoody@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;
use Arara\Process\Exception\InvalidArgumentException;
use Arara\Process\Exception\LogicException;
use Arara\Process\Pidfile;

/**
 * Action implementation for daemons.
 *
 * @author Henrique Moody <henriquemoody@gmail.com>
 */
class Daemon extends Callback
{
    /**
     * When TRUE the daemon is dying, when FALSE it is not.
     *
     * @var bool
     */
    protected $dying = false;

    /**
     * Daemon options.
     *
     * @var array
     */
    protected $options = [
        // Daemon name
        'name' => 'arara',
        // Lock directory
        'lock_dir' => '/var/run',
        // Work directory
        'work_dir' => '/',
        // Default umask value
        'umask' => 0,
        // Default UID value
        'user_id' => null,
        // Default GID value
        'group_id' => null,
        // STDIN file path
        'stdin' => '/dev/null',
        // STDOUT file path
        'stdout' => '/dev/null',
        // STDERR file path
        'stderr' => '/dev/null',
    ];

    /**
     * @param callable $callback Payload callback.
     * @param array    $options  Daemon options.
     */
    public function __construct($callback, array $options = [])
    {
        parent::__construct($callback);

        $this->setOptions($options);
        $this->bindDefaultTriggers();
    }

    /**
     * Returns TRUE when is dying or false if it's not.
     *
     * @return bool
     */
    public function isDying()
    {
        return $this->dying;
    }

    /**
     * Set daemon as dying (or not).
     *
     * @param bool $isDying Default is TRUE.
     */
    public function setAsDying($isDying = true)
    {
        $this->dying = $isDying;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Control $control, Context $context)
    {
        return call_user_func($this->callback, $control, $context, $this);
    }

    /**
     * Binds some callbacks as default triggers.
     */
    protected function bindDefaultTriggers()
    {
        $this->handlers[self::EVENT_INIT]   = $this->fluentCallback([$this, 'handleInit']);
        $this->handlers[self::EVENT_FORK]   = $this->fluentCallback([$this, 'handleFork']);
        $this->handlers[self::EVENT_START]  = $this->fluentCallback([$this, 'handleStart']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException When event binding is forbidden.
     */
    public function bind($event, callable $handler)
    {
        if (in_array($event, [self::EVENT_INIT, self::EVENT_FORK, self::EVENT_START])) {
            throw new InvalidArgumentException('You can not bind a callback for this event');
        }

        parent::bind($event, $handler);
    }

    /**
     * Default trigger for EVENT_INIT.
     *
     * @param Control $control
     * @param Context $context
     */
    public function handleInit(Control $control, Context $context)
    {
        if (! $context->pidfile instanceof Pidfile) {
            $context->pidfile = new Pidfile($control, $this->getOption('name'), $this->getOption('lock_dir'));
        }
        $context->isRunning = $context->pidfile->isActive();
        $context->processId = $context->pidfile->getProcessId();
    }

    /**
     * Default trigger for EVENT_FORK.
     *
     * Finishes the parent process.
     *
     * @param Control $control
     */
    public function handleFork(Control $control)
    {
        $control->flush(0.5);
    }

    /**
     * Default trigger for EVENT_START.
     *
     * - Activates the circular reference collector
     * - Detach session
     * - Reset umask
     * - Update work directory
     * - Close file descriptors
     * - Define process owner, if any
     * - Define process group, if any
     * - Create new file descriptors
     * - Create pidfile
     * - Define pidfile cleanup
     *
     * @param Control $control
     * @param Context $context
     */
    public function handleStart(Control $control, Context $context)
    {
        if (! $context->pidfile instanceof Pidfile) {
            throw new LogicException('Pidfile is not defined');
        }

        // Activates the circular reference collector
        gc_enable();

        // Callback for handle when process is terminated
        $control->signal()->prependHandler(SIGTERM, function () use ($context) {
            $this->setAsDying();
            $context->pidfile->finalize();
        });
        $control->signal()->setHandler(SIGTSTP, SIG_IGN);
        $control->signal()->setHandler(SIGTTOU, SIG_IGN);
        $control->signal()->setHandler(SIGTTIN, SIG_IGN);
        $control->signal()->setHandler(SIGHUP, SIG_IGN);

        // Detach session
        $control->info()->detachSession();

        // Reset umask
        @umask($this->getOption('umask'));

        // Update work directory
        @chdir($this->getOption('work_dir'));

        // Close file descriptors
        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);

        // Define process owner
        if (null !== ($userId = $this->getOption('user_id'))) {
            $control->info()->setUserId($userId);
        }

        // Define process group
        if (null !== ($groupId = $this->getOption('group_id'))) {
            $control->info()->setGroupId($groupId);
        }

        // Create new file descriptors
        $context->stdin = fopen($this->getOption('stdin'), 'r');
        $context->stdout = fopen($this->getOption('stdout'), 'wb');
        $context->stderr = fopen($this->getOption('stderr'), 'wb');

        // Create pidfile
        $context->pidfile->initialize();
    }

    /**
     * Returns all defined options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Defines daemon options.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * Defines an option for daemon.
     *
     *
     * @param string $name  Option name.
     * @param mixed  $value Option value.
     *
     * @throws InvalidArgumentException When option is not valid.
     */
    public function setOption($name, $value)
    {
        if (! array_key_exists($name, $this->options)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid option', $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * Returns the value of a defined option.
     *
     * @param string $name Option name.
     *
     * @return mixed
     */
    public function getOption($name)
    {
        $default = null;
        if (isset($this->options[$name])) {
            $default = $this->options[$name];
        }

        return $default;
    }
}
