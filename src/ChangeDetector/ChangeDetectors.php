<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class ChangeDetectors implements ChangeDetector
{
    /**
     * @param non-empty-array<non-empty-string, ChangeDetector> $deduplicated
     */
    private function __construct(
        private readonly array $deduplicated,
    ) {}

    /**
     * @param non-empty-array<ChangeDetector> $changeDetectors
     */
    public static function from(array $changeDetectors): ChangeDetector
    {
        if (\count($changeDetectors) === 1) {
            return reset($changeDetectors);
        }

        $deduplicated = [];

        foreach ($changeDetectors as $changeDetector) {
            foreach ($changeDetector->deduplicate() as $key => $innerChangeDetector) {
                $deduplicated[$key] = $innerChangeDetector;
            }
        }

        return new self($deduplicated);
    }

    public function changed(): bool
    {
        foreach ($this->deduplicated as $changeDetector) {
            if ($changeDetector->changed()) {
                return true;
            }
        }

        return false;
    }

    public function deduplicate(): array
    {
        return $this->deduplicated;
    }
}