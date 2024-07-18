<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use Typhoon\Reflection\Internal\NativeAdapter\ToNativeTypeConverter;
use Typhoon\Type\TypeVisitor;
use Typhoon\Type\Visitor\DefaultTypeVisitor;
use Typhoon\Type\Visitor\RecursiveTypeReplacer;
use Typhoon\Type\Visitor\RelativeClassTypeResolver;
use Typhoon\Type\Visitor\TemplateTypeResolver;
use Typhoon\Type\Visitor\TypeStringifier;

return (new Config())
    ->setFinder(Finder::create()->append([
        (new ReflectionClass(ToNativeTypeConverter::class))->getFileName(),
        (new ReflectionClass(DefaultTypeVisitor::class))->getFileName(),
        (new ReflectionClass(RecursiveTypeReplacer::class))->getFileName(),
        (new ReflectionClass(RelativeClassTypeResolver::class))->getFileName(),
        (new ReflectionClass(TemplateTypeResolver::class))->getFileName(),
        (new ReflectionClass(TypeStringifier::class))->getFileName(),
    ]))
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setCacheFile(__DIR__ . '/var/' . basename(__FILE__, '.dist.php') . '.cache')
    ->setRules([
        'ordered_class_elements' => ['order' => [
            'case',
            'constant',
            'property',
            'construct',
            ...array_map(
                static fn(ReflectionMethod $method): string => 'method:' . $method->name,
                (new ReflectionClass(TypeVisitor::class))->getMethods(),
            ),
        ]],
    ]);
