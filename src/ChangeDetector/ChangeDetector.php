<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
interface ChangeDetector
{
    public function changed(): bool;

    /**
     * This method should return deduplicated underlying change detectors with their hashes as keys.
     *
     * @see FileChangeDetector::deduplicate()
     * @see ChangeDetectors::from()
     *
     * @return non-empty-array<non-empty-string, self>
     */
    public function deduplicate(): array;
}
