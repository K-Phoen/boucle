<?php

declare(strict_types=1);

namespace Boucle;

use MyCLabs\Enum\Enum;

final class Transport extends Enum
{
    public const BUS = 'bus';

    public const CAR = 'car';

    public const PLANE = 'plane';

    public const BOAT = 'boat';

    public const TRAIN = 'train';

    public const WALKING = 'walking';

    /**
     * @return string[]
     */
    public static function consts(): array
    {
        return array_values(array_map(function (Transport $transport) {
            return $transport->getValue();
        }, static::values()));
    }
}
