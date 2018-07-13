<?php

declare(strict_types=1);

namespace Tests\Boucle;

use Boucle\Boucle;
use Boucle\Coordinates;
use Boucle\Step;
use Boucle\Place;
use Boucle\Transport;
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

    public function testAnEmptyBoucleAsNoStartingTransport(): void
    {
        $boucle = new Boucle('Travelr', Boucle::MAP_MAPBOX, 'api-key', []);

        $this->expectException(\LogicException::class);

        $boucle->startBy();
    }

    public function testTheFirstTransportMethodCanBeRetrieved(): void
    {
        $boucle = new Boucle('Travelr', Boucle::MAP_MAPBOX, 'api-key', [
            new Step(
                new Place('somewhere', new Coordinates(0, 0)),
                new Place('somewhere else', new Coordinates(0, 0)),
                new \DateTimeImmutable(),
                $transport = Transport::PLANE()
            ),
        ]);

        $this->assertSame($transport, $boucle->startBy());
    }

    public function testAnEmptyBoucleAsNoStartingStep(): void
    {
        $boucle = new Boucle('Travelr', Boucle::MAP_MAPBOX, 'api-key', []);

        $this->expectException(\LogicException::class);

        $boucle->startFrom();
    }

    public function testTheFirstPlaceCanBeRetrieved(): void
    {
        $boucle = new Boucle('Travelr', Boucle::MAP_MAPBOX, 'api-key', [
            new Step(
                $start = new Place('somewhere', new Coordinates(0, 0)),
                new Place('somewhere else', new Coordinates(0, 0)),
                new \DateTimeImmutable(),
                Transport::PLANE()
            ),
        ]);

        $this->assertSame($start, $boucle->startFrom());
    }
}
