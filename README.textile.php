<?php

/** Process **/
require_once ('Console/Process.php');

try {

    $process = new Console\Process();
    $process->setMaxChildren(5);
    $users   = array(
        posix_getpwnam('bruno'),
        posix_getpwnam('daniel'),
        posix_getpwnam('henrique'),
        posix_getpwnam('pascutti'),
        posix_getpwnam('wesley'),
    );
    for ($value = 1; $value <= 10; $value++) {
        $user   = $users[rand(0, 4)];
        $fork   = $process->fork(
            function () use ($value, $user) {
                $value  = sprintf('%02d', $value);
                $data   = 'Doing work job ' . $value;
                $file   = sys_get_temp_dir();
                $file   .= "/fork-{$user['name']}-{$value}";
                echo $data . PHP_EOL;
                echo $file . PHP_EOL;
                file_put_contents($file, $data);
                sleep((int) $value);
            }, 
            $user['uid'], 
            $user['gid']
        );
        echo 'Waiting' . PHP_EOL;
        while ($fork->isRunning()) {
            usleep(100000);
            echo '.';
        }
        
        // Ensures that the process will not continue active 
        // and that shared memory is cleared
        $fork->stop();
        echo PHP_EOL;
    }

} catch (Exception $exception) {

    echo $exception->getMessage() . PHP_EOL;
    echo $exception->getTraceAsString();

}
echo PHP_EOL;

