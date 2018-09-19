<?php

declare(strict_types=1);

namespace Boucle;

final class Album
{
    /** @var Image */
    private $cover;

    /** @var iterable|Image[] */
    private $images;

    /** @var string */
    private $path;

    /**
     * @param iterable|Image[] $images
     */
    public function __construct(string $path, Image $cover, iterable $images)
    {
        $this->path = $path;
        $this->cover = $cover;
        $this->images = $images;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function pathRelativeTo(string $directory): string
    {
        return trim(str_replace($directory, '', $this->path), '/');
    }

    public function cover(): Image
    {
        return $this->cover;
    }

    /**
     * @return iterable|Image[]
     */
    public function images(): iterable
    {
        return $this->images;
    }
}
