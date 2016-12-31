<?php

$header = <<<'EOF'
Cerberus IRCBot
Copyright (C) 2008 - 2017 Stefan Hüsges

This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the Free
Software Foundation; either version 3 of the License, or (at your option)
any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, see <http://www.gnu.org/licenses/>.
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@PSR2' => true,
        'combine_consecutive_unsets' => true,
        'function_typehint_space' => true,
        'header_comment' => array('header' => $header),
        'include' => true,
        'method_separation' => true,
        'no_alias_functions' => true,
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'no_short_echo_tag' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_strict' => true,
        'print_to_echo' => true,
        'short_array_syntax' => true,
        'single_quote' => true,
        'standardize_not_equals' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'whitespace_after_comma_in_array' => true,
        'concat_with_spaces' => true,
    ))
    ->finder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
;
