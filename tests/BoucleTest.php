<?php

declare(strict_types=1);

namespace Tests\Boucle;

use Boucle\Boucle;
use PHPUnit\Framework\TestCase;

class BoucleTest extends TestCase
{
    /**
     * @dataProvider invalidValuesProvider
     */
    public function testItCanNotBeConstructedWithInvalidValues(string $mapProvider, string $mapApiKey): void
    {
        $this->expectException(\DomainException::class);

        new Boucle('Travelr', $mapProvider, $mapApiKey, []);
    }

    public function invalidValuesProvider()
    {
        return [
            [Boucle::MAP_MAPBOX, ''],
            ['invalid-map', ''],
        ];
    }
}
