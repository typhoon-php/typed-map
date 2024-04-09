<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use Composer\InstalledVersions;

/**
 * @api
 */
final class PackageChangeDetector implements ChangeDetector
{
    /**
     * @param non-empty-string $package
     * @param non-empty-string $reference
     */
    public function __construct(
        private readonly string $package,
        private readonly string $reference,
    ) {}

    /**
     * @param non-empty-string $package
     */
    public static function tryFromPackage(string $package): ?self
    {
        $version = self::tryGetReference($package);

        if ($version === null) {
            return null;
        }

        return new self($package, $version);
    }

    /**
     * @param non-empty-string $package
     * @return ?non-empty-string
     */
    private static function tryGetReference(string $package): ?string
    {
        if (!class_exists(InstalledVersions::class)) {
            return null;
        }

        try {
            /** @var non-empty-string */
            return InstalledVersions::getReference($package);
        } catch (\Throwable) {
            return null;
        }
    }

    public function changed(): bool
    {
        return self::tryGetReference($this->package) !== $this->reference;
    }

    public function deduplicate(): array
    {
        return [$this->package . '#' . self::class => $this];
    }
}
