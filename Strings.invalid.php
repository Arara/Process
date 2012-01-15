<?php

/**
 * Data provider of invalid strings.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_chunk(
    array(
        true,
        new \stdClass(),
        array(),
        34567899,
        0.9,
        null,
    ),
    1
);

