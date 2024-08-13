<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'concat_space' => ['spacing' => 'one'],
        'explicit_indirect_variable' => true,
        'explicit_string_variable' => true,
        'fully_qualified_strict_types' => ['phpdoc_tags' => []],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'increment_style' => false,
        'list_syntax' => true,
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'no_null_property_initialization' => false,
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => false,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'nullable_type_declaration' => ['syntax' => 'question_mark'],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => true],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_line_span' => ['const' => 'single', 'property' => 'single'],
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_var_annotation_correct_order' => true,
        'protected_to_private' => false,
        'simple_to_complex_string_variable' => true,
        'simplified_if_return' => true,
        'ternary_to_null_coalescing' => true,
        'yoda_style' => false,
    ]);
