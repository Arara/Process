h1. PHProcess

Example:

<pre>
<?php
require_once ('PHProcess/Console/Process.php');
require_once ('PHProcess/Console/Process/Fork.php');
require_once ('PHProcess/Console/Process/Memory.php');

try {

    $process = new PHProcess\Console\Process();
    $process->setMaxChildren(10);

    // Linux users
    exec("awk -F ':' '{ print $1,$3,$4 }' /etc/passwd", $users);
    $users = array_filter($users);
    foreach($users as $key => $user) {
        list($username, $uid, $gid) = explode(' ', $user);
        $fork   = $process->fork(
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
            $uid,
            $gid
        );
    }

} catch (Exception $exception) {

    echo $exception->getMessage() . PHP_EOL;
    echo $exception->getTraceAsString();

}
echo PHP_EOL;

</pre>