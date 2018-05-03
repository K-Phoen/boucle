<?php

declare(strict_types=1);

namespace Boucle;

final class Place
{
    /** @var string */
    private $name;

    /** @var Coordinates */
    private $coordinates;

    public function __construct(string $name, Coordinates $coordinates)
    {
        $this->name = $name;
        $this->coordinates = $coordinates;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function latitude(): float
    {
        return $this->coordinates->latitude();
    }

    public function longitude(): float
    {
        return $this->coordinates->longitude();
    }
}
