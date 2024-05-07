<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use PhpParser\ErrorHandler\Throwing;
use PhpParser\NameContext;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;
use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\ClassId;
use Typhoon\DeclarationId\DeclarationId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\DeclarationId\MethodId;
use Typhoon\Type\Type;
use Typhoon\Type\types;
use function Typhoon\DeclarationId\aliasId;
use function Typhoon\DeclarationId\templateId;

/**
 * @api
 */
final class TypeContext
{
    public readonly ?string $namespace;

    private readonly NameContext $nameContext;

    /**
     * @var array<non-empty-string, true>
     */
    private array $aliasNames;

    /**
     * @var array<non-empty-string, true>
     */
    private array $templateNames;

    /**
     * @param array<non-empty-string> $aliasNames
     * @param array<non-empty-string> $templateNames
     */
    public function __construct(
        ?NameContext $nameContext = null,
        public readonly ?DeclarationId $id = null,
        public readonly null|ClassId|AnonymousClassId $self = null,
        public readonly ?ClassId $parent = null,
        public readonly bool $trait = false,
        array $aliasNames = [],
        array $templateNames = [],
    ) {
        if ($nameContext === null) {
            $this->nameContext = new NameContext(new Throwing());
            $this->nameContext->startNamespace();
            $this->namespace = null;
        } else {
            $this->nameContext = clone $nameContext;
            $this->namespace = $nameContext->getNamespace()?->toString();
        }

        $this->aliasNames = array_fill_keys($aliasNames, true);
        $this->templateNames = array_fill_keys($templateNames, true);
    }

    public static function parseName(string $name): Name
    {
        if ($name === '') {
            throw new \LogicException('Name cannot be empty');
        }

        if ($name[0] === '\\') {
            return new FullyQualified(substr($name, 1));
        }

        if (str_starts_with($name, 'namespace\\')) {
            return new Relative(substr($name, 10));
        }

        return new Name($name);
    }

    public function resolveClass(Name $name): Name
    {
        return $this->nameContext->getResolvedClassName($name);
    }

    /**
     * @param list<Type> $arguments
     */
    public function resolveType(Name $name, array $arguments = []): Type
    {
        if ($name->isSpecialClassName()) {
            return match ($name->toLowerString()) {
                'self' => types::self($this->self, ...$arguments),
                'parent' => types::parent($this->parent, ...$arguments),
                default => types::static($this->self, ...$arguments),
            };
        }

        $stringName = $name->toString();

        if (isset($this->aliasNames[$stringName])) {
            \assert($this->self instanceof ClassId);

            return types::alias(aliasId($this->self, $stringName), ...$arguments);
        }

        if (isset($this->templateNames[$stringName])) {
            if ($arguments !== []) {
                throw new \LogicException('Template type arguments are not supported');
            }

            \assert($this->id instanceof FunctionId
                || $this->id instanceof ClassId
                || $this->id instanceof AnonymousClassId
                || $this->id instanceof MethodId);

            return types::template(templateId($this->id, $stringName));
        }

        return types::object($this->nameContext->getResolvedClassName($name)->toString(), ...$arguments);
    }
}
