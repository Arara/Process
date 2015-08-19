<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->in('examples')
    ->in('src')
    ->in('tests')
    ->name('*.php')
    ->name('*.phpt');

return Symfony\CS\Config\Config::create()
    ->level(\Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(array(
        'blankline_after_open_tag',
        'concat_without_spaces',
        'double_arrow_multiline_whitespaces',
        'duplicate_semicolon',
        'empty_return',
        'extra_empty_lines',
        'include',
        'list_commas',
        'multiline_array_trailing_comma',
        'namespace_no_leading_whitespace',
        'new_with_braces',
        'no_blank_lines_after_class_opening',
        'ordered_use',
        'phpdoc_indent',
        'phpdoc_inline_tag',
        'phpdoc_no_empty_return',
        'phpdoc_order',
        'phpdoc_params',
        'phpdoc_scalar',
        'phpdoc_separation',
        'phpdoc_short_description',
        'phpdoc_trim',
        'return',
        'short_array_syntax',
    ))
    ->finder($finder);
