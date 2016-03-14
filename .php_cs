<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@PSR2' => true,
        'combine_consecutive_unsets' => true,
        'long_array_syntax' => false,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_strict' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ))
    ->finder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
;