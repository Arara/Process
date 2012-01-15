<?php

/**
 * Data provider of invalid integers.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_chunk(
    array(
        new \stdClass(),
        array(),
        '****',
        0.9,
        null,
    ),
    1
);


