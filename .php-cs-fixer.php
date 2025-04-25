<?php

return (new PhpCsFixer\Config())
    ->setRules([
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
            'operators' => [
                '=' => null,
                '=>' => null,
                '??=' => null,
            ],
        ],
        'blank_line_after_opening_tag' => true,
        'visibility_required' => ['elements' => ['method', 'property']],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_summary' => true,
        'no_whitespace_in_blank_line' => true,
        'no_trailing_whitespace' => true,
        'single_blank_line_at_eof' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src') // Diretório onde o código está localizado
    );