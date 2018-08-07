<?php

declare(strict_types=1);

namespace Boucle;

use MyCLabs\Enum\Enum;

final class StepType extends Enum
{
    public const DAY_TRIP = 'daytrip';

    public const HIKE = 'hike';

    public const TRIP = 'trip';

    /**
     * @return string[]
     */
    public static function consts(): array
    {
        return array_values(array_map(function (StepType $type) {
            return $type->getValue();
        }, static::values()));
    }

    public static function isSingleDay(StepType $stepType): bool
    {
        return \in_array($stepType->getValue(), [self::DAY_TRIP, self::HIKE], true);
    }
}
