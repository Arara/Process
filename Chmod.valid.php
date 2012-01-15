<?php

/**
 * Data provider of valid `chmod` definitions.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_chunk(
    array(
        0777,
        0755,
        1754,
        'u+rws,og-rwx',
        'ug+rws,o+rx',
        'a+rx,u+w',
    ),
    1
);

