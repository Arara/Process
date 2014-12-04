<?php

namespace Arara\Process\Control;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Control\Info
 */
class InfoTest extends TestCase
{
    public function testShouldReturnCurrentProcessId()
    {
        $expectedProcessId = 123456;

        $this->overwrite(
            'posix_getpid',
            function () use ($expectedProcessId) {
                return $expectedProcessId;
            }
        );

        $info = new Info();
        $actualProcessId = $info->getId();

        $this->assertEquals($expectedProcessId, $actualProcessId);
    }

    public function testShouldProcessIdParentProcessId()
    {
        $expectedParentProcessId = 654321;

        $this->overwrite(
            'posix_getppid',
            function () use ($expectedParentProcessId) {
                return $expectedParentProcessId;
            }
        );

        $info = new Info();
        $actualParentProcessId = $info->getParentId();

        $this->assertEquals($expectedParentProcessId, $actualParentProcessId);
    }

    public function testShouldReturnCurrentUserId()
    {
        $expectedUserId = 1001;

        $this->overwrite(
            'posix_getuid',
            function () use ($expectedUserId) {
                return $expectedUserId;
            }
        );

        $info = new Info();
        $actualUserId = $info->getUserId();

        $this->assertEquals($expectedUserId, $actualUserId);
    }

    public function testShouldDefineCurrentUserId()
    {
        $actualUserId = null;
        $expectedUserId = 1001;

        $this->overwrite(
            'posix_setuid',
            function ($userId) use (&$actualUserId) {
                $actualUserId = $userId;

                return true;
            }
        );

        $info = new Info();
        $info->setUserId($expectedUserId);

        $this->assertEquals($expectedUserId, $actualUserId);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Unable to update the current user identifier
     */
    public function testShouldThrowsAnExceptionWhenUnableToUpdateCurrentUserId()
    {
        $this->overwrite(
            'posix_setuid',
            function () {
                return false;
            }
        );

        $info = new Info();
        $info->setUserId(1001);
    }

    public function testShouldReturnCurrentUserName()
    {
        $expectedUserName = 'arara';

        $this->overwrite(
            'posix_getlogin',
            function () use ($expectedUserName) {
                return $expectedUserName;
            }
        );

        $info = new Info();
        $actualUserName = $info->getUserName();

        $this->assertEquals($expectedUserName, $actualUserName);
    }

    public function testShouldDefineCurrentUserName()
    {
        $actualUserId = null;
        $expectedUserId = 1001;

        $this->overwrite(
            'posix_getpwnam',
            function () use ($expectedUserId) {
                return array('uid' => $expectedUserId);
            }
        );

        $this->overwrite(
            'posix_setuid',
            function ($userId) use (&$actualUserId) {
                $actualUserId = $userId;

                return true;
            }
        );

        $info = new Info();
        $info->setUserName('arara');

        $this->assertEquals($expectedUserId, $actualUserId);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage "arara" is not a valid user name
     */
    public function testShouldThrowsAnExceptionWhenDefiningANonExistingUserName()
    {
        $this->overwrite(
            'posix_getpwnam',
            function () {
                return false;
            }
        );

        $info = new Info();
        $info->setUserName('arara');
    }

    public function testShouldReturnCurrentGroupId()
    {
        $expectedGroupId = 1005;

        $this->overwrite(
            'posix_getgid',
            function () use ($expectedGroupId) {
                return $expectedGroupId;
            }
        );

        $info = new Info();
        $actualGroupId = $info->getGroupId();

        $this->assertEquals($expectedGroupId, $actualGroupId);
    }

    public function testShouldDefineCurrentGroupId()
    {
        $actualGroupId = null;
        $expectedGroupId = 1005;

        $this->overwrite(
            'posix_setgid',
            function ($groupId) use (&$actualGroupId) {
                $actualGroupId = $groupId;

                return true;
            }
        );

        $info = new Info();
        $info->setGroupId($expectedGroupId);

        $this->assertEquals($expectedGroupId, $actualGroupId);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Unable to update the current group identifier
     */
    public function testShouldThrowsAnExceptionWhenUnableToUpdateCurrentGroupId()
    {
        $this->overwrite(
            'posix_setgid',
            function () {
                return false;
            }
        );

        $info = new Info();
        $info->setGroupId(1001);
    }

    public function testShouldReturnCurrentGroupName()
    {
        $currentGroupId = 1004;
        $expectedGroupName = 'arara';

        $this->overwrite(
            'posix_getgid',
            function () use ($currentGroupId) {
                return $currentGroupId;
            }
        );

        $this->overwrite(
            'posix_getgrgid',
            function ($groupId) use ($currentGroupId, $expectedGroupName) {
                if ($currentGroupId != $groupId) {
                    return array();
                }

                return array('name' => $expectedGroupName);
            }
        );

        $info = new Info();
        $actualGroupName = $info->getGroupName();

        $this->assertEquals($expectedGroupName, $actualGroupName);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Unable to get the current group name
     */
    public function testShouldThrowsAnExceptionWhenUnadleToFindCurrentGroupName()
    {
        $this->overwrite(
            'posix_getgrgid',
            function () {
                return array();
            }
        );

        $info = new Info();
        $info->getGroupName();
    }


    public function testShouldDefineCurrentGroupName()
    {
        $actualGroupId = null;
        $expectedGroupId = 1004;

        $this->overwrite(
            'posix_getgrnam',
            function () use ($expectedGroupId) {
                return array('gid' => 1004);
            }
        );

        $this->overwrite(
            'posix_setgid',
            function ($groupId) use (&$actualGroupId) {
                $actualGroupId = $groupId;

                return true;
            }
        );

        $info = new Info();
        $info->setGroupName('arara');

        $this->assertEquals($expectedGroupId, $actualGroupId);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage "arara" is not a valid group name
     */
    public function testShouldThrowsAnExceptionWhenDefiningANonExistingGroupName()
    {
        $this->overwrite(
            'posix_getgrnam',
            function () {
                return array();
            }
        );

        $info = new Info();
        $info->setGroupName('arara');
    }

    public function testShouldDetachSessionOfCurrentProcess()
    {
        $expectedSessionId = 1;

        $this->overwrite(
            'posix_setsid',
            function () use ($expectedSessionId) {
                return $expectedSessionId;
            }
        );

        $info = new Info();
        $actualSessionId = $info->detachSession();

        $this->assertEquals($expectedSessionId, $actualSessionId);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Unable to detach current session
     */
    public function testShouldThrowsAnExceptionWhenUnableToDetachSessionOfTheCurrentProcess()
    {
        $this->overwrite(
            'posix_setsid',
            function () {
                return -1;
            }
        );

        $info = new Info();
        $info->detachSession();
    }

    public function testShouldReturnCurrentSessionId()
    {
        $expectedSessionId = 1;

        $this->overwrite(
            'posix_getsid',
            function () use ($expectedSessionId) {
                return $expectedSessionId;
            }
        );

        $info = new Info();
        $actualSessionId = $info->getSessionId();

        $this->assertEquals($expectedSessionId, $actualSessionId);
    }
}
