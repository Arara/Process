<?php

namespace Arara\Process\Control;

use InvalidArgumentException;
use RuntimeException;

class Info
{
    /**
     * Returns the current process identifier.
     *
     * @return int
     */
    public function getId()
    {
        return posix_getpid();
    }

    /**
     * Returns the parent process identifier.
     *
     * @return int
     */
    public function getParentId()
    {
        return posix_getppid();
    }

    /**
     * Returns the current user identifier.
     *
     * @return int
     */
    public function getUserId()
    {
        return posix_getuid();
    }

    /**
     * Defines the current user identifier.
     *
     * @throws RuntimeException When unable to update current user identifier.
     * @param  int $userId Unix user identifier.
     * @return void
     */
    public function setUserId($userId)
    {
        if (! posix_setuid($userId)) {
            throw new RuntimeException('Unable to update the current user identifier');
        }
    }

    /**
     * Returns the current user name.
     *
     * @return string
     */
    public function getUserName()
    {
        return posix_getlogin();
    }

    /**
     * Defines the current user name.
     *
     * @throws RuntimeException When unable to update current user name.
     * @param  string $userName Unix user name.
     * @return void
     */
    public function setUserName($userName)
    {
        $user = posix_getpwnam($userName);
        if (! isset($user['uid'])) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid user name', $userName));
        }

        $this->setUserId($user['uid']);
    }

    /**
     * Returns the current group identifier.
     *
     * @return int
     */
    public function getGroupId()
    {
        return posix_getgid();
    }

    /**
     * Defines the current group identifier.
     *
     * @throws RuntimeException When unable to update current group identifier.
     * @param  int $groupId Unix group identifier.
     * @return void
     */
    public function setGroupId($groupId)
    {
        if (! posix_setgid($groupId)) {
            throw new RuntimeException('Unable to update the current group identifier');
        }
    }

    /**
     * Returns the current group name.
     *
     * @throws RuntimeException When unable to get current group name.
     * @return string
     */
    public function getGroupName()
    {
        $group = posix_getgrgid($this->getGroupId());
        if (! isset($group['name'])) {
            throw new RuntimeException('Unable to get the current group name');
        }

        return $group['name'];
    }

    /**
     * Defines the current group name.
     *
     * @throws RuntimeException When unable to update current group name.
     * @param  string $groupName Unix group name.
     * @return void
     */
    public function setGroupName($groupName)
    {
        $group = posix_getgrnam($groupName);
        if (! isset($group['gid'])) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid group name', $groupName));
        }

        $this->setGroupId($group['gid']);
    }

    /**
     * Detach process from the current session and make the current process a session leader.
     *
     * @throws RuntimeException When unable to detach current session
     * @return int Session identifier.
     */
    public function detachSession()
    {
        $session = posix_setsid();
        if (-1 == $session) {
            throw new RuntimeException('Unable to detach current session');
        }

        return $session;
    }

    /**
     * Returns the current session identifier.
     *
     * @return int
     */
    public function getSessionId()
    {
        return posix_getsid($this->getId());
    }
}
