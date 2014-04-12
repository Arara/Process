<?php

namespace Arara\Process;

function buildFunctionBody($function)
{
    $body = <<<'EOD'
function %{function}()
{
    static $count;
    if (! isset($GLOBALS['arara']['%{function}'])) {
        $count = 0;
    }
    $count++;

    $args = func_get_args();
    $return = null;

    $GLOBALS['arara']['%{function}']['args'] = $args;
    if (isset($GLOBALS['arara']['%{function}']['return'])) {
        $return = $GLOBALS['arara']['%{function}']['return'];
    }
    $GLOBALS['arara']['%{function}']['count'] = $count;

    return $return;
}
EOD;

    return str_replace('%{function}', $function, $body) . PHP_EOL;
}

$functions = get_defined_functions();
foreach ($functions['internal'] as $function) {
    if (false === strpos($function, 'pcntl')
        && false === strpos($function, 'posix')) {
        continue;
    }

    echo PHP_EOL . buildFunctionBody($function);
}
