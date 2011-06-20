<?php

/** Process **/
require_once ('Console/Process.php');

try {

    $process = new Console\Process(5);
    $users   = array(
        posix_getpwnam('daniel'),
        posix_getpwnam('henrique'),
        posix_getpwnam('pascutti'),
        posix_getpwnam('wesley'),
    );
    for ($value = 1; $value <= 12; $value++) {
        $user   = $users[rand(0, 3)];
        $process->fork(
            function () use ($value, $user) {
                $value  = sprintf('%02d', $value);
                $data   = 'Doing work job ' . $value;
                $file   = sys_get_temp_dir();
                $file   .= '/fork-' . $value . '-' . $user['name'];
                echo $data . PHP_EOL;
                echo $file . PHP_EOL;
                file_put_contents($file, $data);
            }, 
            $user['uid'], 
            $user['gid']
        );
    }

} catch (Exception $exception) {

    echo $exception->getMessage() . PHP_EOL;
    echo $exception->getTraceAsString();

}
echo PHP_EOL;

