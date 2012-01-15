<?php

/**
 * Data provider of hash algos.
 *
 * @author  Henrique Moody <henriquemoody@gmail.com>
 * @return  array
 */
return array_chunk(hash_algos(), 1);