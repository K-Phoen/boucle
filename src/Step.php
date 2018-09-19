<?php

declare(strict_types=1);

namespace Boucle;

final class Step
{
    /** @var Place */
    private $from;

    /** @var Place */
    private $to;

    /** @var \DateTimeImmutable */
    private $date;

    /** @var Album */
    private $album;

    /** @var Transport */
    private $transport;

    /** @var string */
    private $pathFilename;

    public function __construct(Place $from, Place $to, \DateTimeImmutable $date, Transport $transport, string $pathFilename = '')
    {
        $this->from = $from;
        $this->to = $to;
        $this->date = $date;
        $this->transport = $transport;
        $this->pathFilename = $pathFilename;
    }

    public function withAlbum(Album $album): self
    {
        $step = clone $this;
        $step->album = $album;

        return $step;
    }

    public function from(): Place
    {
        return $this->from;
    }

    public function to(): Place
    {
        return $this->to;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function transport(): Transport
    {
        return $this->transport;
    }

    public function pathFilename(): string
    {
        return $this->pathFilename;
    }

    public function hasAlbum(): bool
    {
        return $this->album !== null;
    }

    public function album(): Album
    {
        if (!$this->album) {
            throw new \LogicException('No album for this step');
        }

        return $this->album;
    }
}
