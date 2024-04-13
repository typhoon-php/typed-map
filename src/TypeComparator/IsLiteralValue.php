<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\ClassId;
use Typhoon\Type\Type;
use Typhoon\Type\Visitor\DefaultTypeVisitor;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsLiteralValue extends Comparator
{
    public function __construct(
        private readonly float|bool|int|string $value,
    ) {}

    public function classConstant(Type $self, Type $class, string $name): mixed
    {
        // TODO full class constant support.
        return $name === 'class' && \is_string($this->value) && $this->value === $class->accept(
            new /** @extends DefaultTypeVisitor<?string> */ class () extends DefaultTypeVisitor {
                public function namedObject(Type $self, ClassId|AnonymousClassId $class, array $arguments): mixed
                {
                    return $class->name;
                }

                protected function default(Type $self): mixed
                {
                    return null;
                }
            },
        );
    }

    public function literalValue(Type $self, float|bool|int|string $value): mixed
    {
        return $value === $this->value;
    }
}
