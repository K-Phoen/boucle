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
}
