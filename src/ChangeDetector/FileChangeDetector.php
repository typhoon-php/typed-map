<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class FileChangeDetector implements ChangeDetector
{
    /**
     * @param non-empty-string $file
     * @param false|non-empty-string $md5
     */
    public function __construct(
        private readonly string $file,
        private readonly false|int $mtime,
        private readonly false|string $md5,
    ) {}

    /**
     * @param non-empty-string $path
     */
    public static function fromPath(string $path): self
    {
        $handle = @fopen($path, 'r');

        if ($handle === false) {
            return new self($path, false, false);
        }

        try {
            if (!@flock($handle, LOCK_SH)) {
                throw new \RuntimeException('Failed to acquire a shared lock on ' . $path);
            }

            $mtime = @filemtime($path);

            if ($mtime === false) {
                return new self($path, false, false);
            }

            $md5 = @md5_file($path);

            if ($md5 === false) {
                return new self($path, false, false);
            }

            return new self($path, $mtime, $md5);
        } finally {
            @fclose($handle);
        }
    }

    /**
     * @param non-empty-string $path
     */
    public static function fromPathEnsureExists(string $path): self
    {
        $handle = @fopen($path, 'r');

        if ($handle === false) {
            throw new FileIsNotReadable($path);
        }

        try {
            if (!@flock($handle, LOCK_SH)) {
                throw new \RuntimeException('Failed to acquire a shared lock on ' . $path);
            }

            $mtime = @filemtime($path);

            if ($mtime === false) {
                throw new FileIsNotReadable($path);
            }

            $md5 = @md5_file($path);

            if ($md5 === false) {
                throw new FileIsNotReadable($path);
            }

            return new self($path, $mtime, $md5);
        } finally {
            @fclose($handle);
        }
    }

    public function changed(): bool
    {
        return @filemtime($this->file) !== $this->mtime
            || @md5_file($this->file) !== $this->md5;
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
        return sprintf('%d:%s:%s:file', (string) $this->mtime, (string) $this->md5, $this->file);
    }
}
