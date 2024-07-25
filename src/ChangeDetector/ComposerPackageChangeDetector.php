<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use Composer\InstalledVersions;

/**
 * @api
 */
final class ComposerPackageChangeDetector implements ChangeDetector
{
    /**
     * @param non-empty-string $name
     * @param ?non-empty-string $reference
     */
    private function __construct(
        private readonly string $name,
        private readonly ?string $reference,
    ) {}

    /**
     * @param non-empty-string $name
     */
    public static function fromName(string $name): self
    {
        return new self($name, self::getReference($name));
    }

    /**
     * @param non-empty-string $name
     * @throws PackageIsNotInstalled
     */
    public static function fromNameEnsureInstalled(string $name): self
    {
        return new self($name, self::getReference($name) ?? throw new PackageIsNotInstalled($name));
    }

    /**
     * @param non-empty-string $name
     * @return ?non-empty-string
     */
    private static function getReference(string $name): ?string
    {
        try {
            /** @var non-empty-string */
            return InstalledVersions::getReference($name);
        } catch (\OutOfBoundsException) {
            return null;
        }
    }

    public function changed(): bool
    {
        return self::getReference($this->name) !== $this->reference;
    }

    public function deduplicate(): array
    {
        return [$this->hash() => $this];
    }

    /**
     * @return non-empty-string
     */
    private function hash(): string
    {
        return \sprintf('%s:%s:composer-package', (string) $this->reference, $this->name);
    }
}
