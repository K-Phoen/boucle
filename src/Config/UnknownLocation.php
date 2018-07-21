<?php

declare(strict_types=1);

namespace Boucle\Config;

class UnknownLocation extends \RuntimeException
{
    public static function fromName(string $name, \Exception $previous = null): self
    {
        return new static(sprintf('Unknown location "%s"', $name), 0, $previous);
    }
}
