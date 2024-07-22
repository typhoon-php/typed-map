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
     * @param non-empty-string $reference
     */
    private function __construct(
        private readonly string $name,
        private readonly string $reference,
    ) {}

    /**
     * @param non-empty-string $name
     */
    public static function tryFromName(string $name): ?self
    {
        $version = self::tryGetReference($name);

        if ($version === null) {
            return null;
        }

        return new self($name, $version);
    }

    /**
     * @param non-empty-string $name
     * @return ?non-empty-string
     */
    private static function tryGetReference(string $name): ?string
    {
        try {
            /** @var non-empty-string */
            return InstalledVersions::getReference($name);
        } catch (\Throwable) {
            return null;
        }
    }

    public function changed(): bool
    {
        return self::tryGetReference($this->name) !== $this->reference;
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
        return $this->reference . ':' . $this->name . ':composer-package';
    }
}
