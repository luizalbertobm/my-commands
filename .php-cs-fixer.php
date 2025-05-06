<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_summary' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/tests')
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/bin')
    );
