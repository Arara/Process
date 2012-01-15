<?php

/**
 * Data provider of valid integers.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_chunk(
    array(
        -6789,
        true,
        array(),
        new stdClass(),
        null,
    ), 
    1
);