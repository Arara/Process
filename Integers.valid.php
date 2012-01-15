<?php

/**
 * Data provider of valid integers.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_chunk(
    array(
        1,
        1234567890,
        9876543212345678,
        -1234,
        0123,
        0x1A,
    ),
    1
);

