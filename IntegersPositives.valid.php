<?php

/**
 * Data provider of valid integers.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_map(
    function ($item) {
        if ($item[0] < 0) {
            $item[0] *= -1;
        } elseif ($item[0] < 0) {
            $item[0] = rand(1,500);
        }
        return $item;
    }, 
    include __DIR__ . '/Integers.valid.php'
);