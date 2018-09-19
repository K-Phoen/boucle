<?php

declare(strict_types=1);

namespace Boucle;

final class Image
{
    /** @var string */
    private $path;

    /** @var string */
    private $thumbnailPath;

    public static function fromPath(string $path, string $thumbnailPath = null): self
    {
        return new static($path, $thumbnailPath ?: $path);
    }

    public static function thumbFrom(self $source, string $thumbPrefix): self
    {
        return static::fromPath($source->path(), $source->directory().'/'.$thumbPrefix.$source->filename());
    }

    private function __construct(string $path, string $thumbnailPath)
    {
        $this->path = $path;
        $this->thumbnailPath = $thumbnailPath;
    }

    public function relativeTo(string $directory): self
    {
        return new static(
            trim(str_replace($directory, '', $this->path), '/'),
            trim(str_replace($directory, '', $this->thumbnailPath), '/')
        );
    }

    public function path(): string
    {
        return $this->path;
    }

    public function thumbnailPath(): string
    {
        return $this->thumbnailPath;
    }

    public function filename(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    public function directory(): string
    {
        return pathinfo($this->path, PATHINFO_DIRNAME);
    }

    public function thumbDirectory(): string
    {
        return pathinfo($this->thumbnailPath, PATHINFO_DIRNAME);
    }
}
