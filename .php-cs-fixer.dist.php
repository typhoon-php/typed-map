<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use PHPyh\CodingStandard\PhpCsFixerCodingStandard;

$config = (new Config())
    ->setFinder(
        Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tools/psalm/src')
            ->append([
                __FILE__,
                __DIR__ . '/.visitors.php-cs-fixer.dist.php',
            ])
            ->append(
                Finder::create()
                    ->in(__DIR__ . '/tests')
                    ->exclude([
                        'Fixtures',
                    ]),
            ),
    )
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/var/' . basename(__FILE__, '.dist.php') . '.cache');

(new PhpCsFixerCodingStandard())->applyTo($config, [
    'final_public_method_for_abstract_class' => false,
    'no_unset_on_property' => false,
    /** @see TypeInheritance */
    'strict_comparison' => false,
    'logical_operators' => false,
    'no_multiline_whitespace_around_double_arrow' => false,
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
