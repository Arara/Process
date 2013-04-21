<?php

/* Bootstrap */
require_once __DIR__ . '/bootstrap.php';

try {

    $manager = new Arara\Process\Manager(10);

    // Linux users
    exec("awk -F ':' '{ print $1,$3,$4 }' /etc/passwd", $users);
    $users = array_filter($users);
    foreach($users as $key => $user) {
        if (0 === strpos($user, '#')) {// Comments
            continue;
        }
        list($username, $uid, $gid) = explode(' ', $user);
        $process = new Arara\Process\Item(
            function () use ($key, $username) {
                $key    = sprintf('%02d', $key);
                $data   = "Doing work job {$key} for {$username}";
                $file   = sys_get_temp_dir();
                $file   .= "/fork-{$username}-{$key}";
                echo $data . PHP_EOL;
                echo $file . PHP_EOL;
                file_put_contents($file, $data);
                sleep(5);
            },
            new Arara\Process\Ipc\SharedMemory(),
            $uid,
            $gid
        );
        $process->setCallback(
            function (Arara\Process\Ipc $ipc) {
                echo $ipc->load('output') . PHP_EOL;
            },
            Arara\Process\Item::STATUS_SUCESS | Arara\Process\Item::STATUS_ERROR | Arara\Process\Item::STATUS_FAIL
        );
        $manager->addChild($process);
    }

} catch (Exception $exception) {

    echo $exception->getMessage() . PHP_EOL;
    echo $exception->getTraceAsString();

}
echo PHP_EOL;