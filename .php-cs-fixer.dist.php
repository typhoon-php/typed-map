<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PHPyh\CodingStandard\PhpCsFixerCodingStandard;

$config = (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tools/psalm/src')
            ->append([__FILE__])
            ->append(
                Finder::create()
                    ->in(__DIR__ . '/tests')
                    ->exclude([
                        'Fixtures',
                    ]),
            ),
    )
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache');

(new PhpCsFixerCodingStandard())->applyTo($config, [
    'final_public_method_for_abstract_class' => false,
    'no_unset_on_property' => false,
    /** @see TypeInheritance */
    'strict_comparison' => false,
    'logical_operators' => false,
    'ordered_class_elements' => ['order' => ['use_trait']],
    'class_attributes_separation' => ['elements' => [
        'trait_import' => 'only_if_meta',
        'const' => 'only_if_meta',
        'case' => 'only_if_meta',
        'property' => 'one',
        'method' => 'one',
    ]],
]);

return $config;
