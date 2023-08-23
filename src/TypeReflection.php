<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Type;
use Typhoon\types;
use Typhoon\TypeVisitor;
use Typhoon\Reflection\Reflector\FriendlyReflection;

/**
 * @api
 */
final class TypeReflection extends FriendlyReflection
{
    public function __construct(
        private readonly ?Type $native,
        private readonly ?Type $phpDoc,
    ) {}

    public function getNative(): ?Type
    {
        return $this->native;
    }

    public function getPhpDoc(): ?Type
    {
        return $this->phpDoc;
    }

    public function resolve(): Type
    {
        return $this->phpDoc ?? $this->native ?? types::mixed;
    }

    protected function withResolvedTypes(TypeVisitor $typeResolver): static
    {
        return new self(
            native: $this->native?->accept($typeResolver),
            phpDoc: $this->phpDoc?->accept($typeResolver),
        );
    }

    protected function toChildOf(FriendlyReflection $parent): static
    {
        if ($this->phpDoc !== null) {
            return $this;
        }

        if ($parent->phpDoc === null) {
            return $this;
        }

        if ($parent->native !== $this->native) {
            return $this;
        }

        return new self(
            native: $this->native,
            phpDoc: $parent->phpDoc,
        );
    }
}
