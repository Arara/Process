<?php

/**
 * Data provider of valid directories.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return call_user_func(function () {
    $directories = array(
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataprovider-1',
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataprovider-2',
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataprovider-3',
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataprovider-4',
        sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dataprovider-5',
    );
    return array_map(
        function ($directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0766, true);
            }
            return array(realpath($directory));
        },
        $directories
    );
});