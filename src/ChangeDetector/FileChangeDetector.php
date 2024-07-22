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
     * @param non-empty-string $md5
     */
    public function __construct(
        private readonly string $file,
        private readonly int $mtime,
        private readonly string $md5,
    ) {}

    /**
     * @param non-empty-string $file
     * @throws FileIsNotReadable
     */
    public static function fromFile(string $file): self
    {
        $handle = @fopen($file, 'r');

        if ($handle === false) {
            throw new FileIsNotReadable($file);
        }

        if (!@flock($handle, LOCK_SH)) {
            throw new \RuntimeException('Failed to acquire shared lock on file ' . $file);
        }

        $mtime = @filemtime($file);

        if ($mtime === false) {
            throw new FileIsNotReadable($file);
        }

        $md5 = @md5_file($file);

        if ($md5 === false) {
            throw new FileIsNotReadable($file);
        }

        fclose($handle);

        return new self($file, $mtime, $md5);
    }

    public function changed(): bool
    {
        return filemtime($this->file) !== $this->mtime || md5_file($this->file) !== $this->md5;
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
        return $this->mtime . ':' . $this->md5 . ':' . $this->file . ':file';
    }
}
