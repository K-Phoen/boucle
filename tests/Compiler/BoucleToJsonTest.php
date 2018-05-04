<?php

declare(strict_types=1);

namespace Tests\Boucle\Compiler;

use Boucle\Boucle;
use Boucle\Compiler\BoucleToJson;
use Boucle\Coordinates;
use Boucle\Place;
use Boucle\Step;
use Boucle\Transport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class BoucleToJsonTest extends TestCase
{
    /** @var Filesystem */
    private $fs;

    /** @var BoucleToJson */
    private $compiler;

    public function setUp(): void
    {
        $this->fs = $this->createMock(Filesystem::class);

        $this->compiler = new BoucleToJson($this->fs);
    }

    public function testItCompilesTheBoucleToJson(): void
    {
        $boucle = new Boucle('Boucle title', Boucle::MAP_MAPBOX, 'api-key', [
            new Step(
                new Place('from-first', new Coordinates(1, 1)),
                new Place('to-first', new Coordinates(1, 2)),
                new \DateTimeImmutable('2018-05-04'),
                new Transport('bus')
            ),
            new Step(
                new Place('from-second', new Coordinates(2, 1)),
                new Place('to-second', new Coordinates(2, 2)),
                new \DateTimeImmutable('2018-05-05'),
                new Transport('train')
            ),
            new Step(
                new Place('from-third', new Coordinates(3, 1)),
                new Place('to-third', new Coordinates(3, 2)),
                new \DateTimeImmutable('2018-05-06'),
                new Transport('bus')
            ),
        ]);

        $this->fs->expects($this->once())
            ->method('dumpFile')
            ->with('/webroot/boucle.json', json_encode([
                'start' => [
                    'from' => [
                        'name' => 'from-first',
                        'lat' => 1,
                        'long' => 1,
                    ],
                    'with' => 'bus',
                ],
                'steps' => [
                    'bus' => [
                        [
                            'from' => [
                                'name' => 'from-first',
                                'lat' => 1,
                                'long' => 1,
                            ],
                            'to' => [
                                'name' => 'to-first',
                                'lat' => 1,
                                'long' => 2,
                            ],
                            'date' => '2018-05-04',
                        ],
                        [
                            'from' => [
                                'name' => 'from-third',
                                'lat' => 3,
                                'long' => 1,
                            ],
                            'to' => [
                                'name' => 'to-third',
                                'lat' => 3,
                                'long' => 2,
                            ],
                            'date' => '2018-05-06',
                        ],
                    ],
                    'car' => [],
                    'plane' => [],
                    'boat' => [],
                    'train' => [
                        [
                            'from' => [
                                'name' => 'from-second',
                                'lat' => 2,
                                'long' => 1,
                            ],
                            'to' => [
                                'name' => 'to-second',
                                'lat' => 2,
                                'long' => 2,
                            ],
                            'date' => '2018-05-05',
                        ],
                    ],
                ],
            ], JSON_PRETTY_PRINT));

        $this->compiler->compile($boucle, '/webroot');
    }
}
