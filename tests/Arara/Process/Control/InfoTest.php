<?php

namespace Arara\Process\Control;

/**
 * @covers Arara\Process\Control\Info
 */
class InfoTest extends \TestCase
{
    public function testShouldReturnCurrentProcessId()
    {
        $GLOBALS['arara']['posix_getpid']['return'] = 123456;
        $info = new Info();

        $this->assertEquals(123456, $info->getId());
    }

    public function testShouldReturnParentProcessId()
    {
        $GLOBALS['arara']['posix_getppid']['return'] = 654321;
        $info = new Info();

        $this->assertEquals(654321, $info->getParentId());
    }

    public function testShouldReturnCurrentUserId()
    {
        $GLOBALS['arara']['posix_getuid']['return'] = 1001;
        $info = new Info();

        $this->assertEquals(1001, $info->getUserId());
    }

    public function testShouldDefineCurrentUserId()
    {
        $GLOBALS['arara']['posix_setuid']['return'] = true;
        $info = new Info();
        $info->setUserId(1001);

        $this->assertEquals(array(1001), $GLOBALS['arara']['posix_setuid']['args']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to update the current user identifier
     */
    public function testShouldThrowsAnExceptionWhenUnableToUpdateCurrentUserId()
    {
        $GLOBALS['arara']['posix_setuid']['return'] = false;
        $info = new Info();
        $info->setUserId(1001);
    }

    public function testShouldReturnCurrentUserName()
    {
        $GLOBALS['arara']['posix_getlogin']['return'] = 'arara';
        $info = new Info();

        $this->assertEquals('arara', $info->getUserName());
    }

    public function testShouldDefineCurrentUserName()
    {
        $GLOBALS['arara']['posix_getpwnam']['return'] = array('uid' => 1001);
        $GLOBALS['arara']['posix_setuid']['return'] = true;
        $info = new Info();
        $info->setUserName('arara');

        $this->assertEquals(array(1001), $GLOBALS['arara']['posix_setuid']['args']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage "arara" is not a valid user name
     */
    public function testShouldThrowsAnExceptionWhenDefiningANonExistingUserName()
    {
        $info = new Info();
        $info->setUserName('arara');
    }

    public function testShouldReturnCurrentGroupId()
    {
        $GLOBALS['arara']['posix_getgid']['return'] = 1005;
        $info = new Info();

        $this->assertEquals(1005, $info->getGroupId());
    }

    public function testShouldDefineCurrentGroupId()
    {
        $GLOBALS['arara']['posix_setgid']['return'] = true;
        $info = new Info();
        $info->setGroupId(1005);

        $this->assertEquals(array(1005), $GLOBALS['arara']['posix_setgid']['args']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to update the current group identifier
     */
    public function testShouldThrowsAnExceptionWhenUnableToUpdateCurrentGroupId()
    {
        $GLOBALS['arara']['posix_setgid']['return'] = false;
        $info = new Info();
        $info->setGroupId(1001);
    }

    public function testShouldReturnCurrentGroupName()
    {
        $GLOBALS['arara']['posix_getgid']['return'] = 1004;
        $GLOBALS['arara']['posix_getgrgid']['return'] = array('name' => 'arara');
        $info = new Info();

        $this->assertEquals('arara', $info->getGroupName());
        $this->assertEquals(array(1004), $GLOBALS['arara']['posix_getgrgid']['args']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to get the current group name
     */
    public function testShouldThrowsAnExceptionWhenUnadleToFindCurrentGroupName()
    {
        $GLOBALS['arara']['posix_getgrgid']['return'] = false;
        $info = new Info();
        $info->getGroupName();
    }


    public function testShouldDefineCurrentGroupName()
    {
        $GLOBALS['arara']['posix_getgrnam']['return'] = array('gid' => 1004);
        $GLOBALS['arara']['posix_setgid']['return'] = true;
        $info = new Info();
        $info->setGroupName('arara');

        $this->assertEquals(array(1004), $GLOBALS['arara']['posix_setgid']['args']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage "arara" is not a valid group name
     */
    public function testShouldThrowsAnExceptionWhenDefiningANonExistingGroupName()
    {
        $info = new Info();
        $info->setGroupName('arara');
    }

    public function testShouldDetachSessionOfCurrentProcess()
    {
        $GLOBALS['arara']['posix_setsid']['return'] = 1;
        $info = new Info();

        $this->assertEquals(1, $info->detachSession());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to detach current session
     */
    public function testShouldThrowsAnExceptionWhenUnableToDetachSessionOfTheCurrentProcess()
    {
        $GLOBALS['arara']['posix_setsid']['return'] = -1;
        $info = new Info();
        $info->detachSession();
    }

    public function testShouldReturnCurrentSessionId()
    {
        $GLOBALS['arara']['posix_getsid']['return'] = 1;
        $info = new Info();

        $this->assertEquals(1, $info->getSessionId());
    }
}
