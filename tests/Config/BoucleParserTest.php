<?php

declare(strict_types=1);

namespace Tests\Boucle\Config;

use Boucle\Boucle;
use Boucle\Config\BoucleParser;
use Boucle\Config\InvalidConfiguration;
use Boucle\Place;
use Boucle\Transport;
use Geocoder\Geocoder;
use Geocoder\Model\Address;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\Coordinates;
use Geocoder\Query\GeocodeQuery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class BoucleParserTest extends TestCase
{
    /** @var Geocoder */
    private $geocoder;

    /** @var vfsStreamDirectory */
    private $root;

    /** @var BoucleParser */
    private $parser;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('root_dir', null, [
            'full_config_file.yaml' => '
boucle:
    title: Europe – 2018

    map_provider: mapbox
    map_api_key: key

    start: Clermont-Ferrand, France

    steps:
        -
            to: Lyon, France
            with: bus
            date: \'2018-05-09\'
        -
            to: Dublin, Ireland
            with: bus
            date: \'2018-05-10\'
',
            'invalid_no_start.yaml' => '
boucle:
    title: Europe – 2018

    steps:
        -
            to: Lyon, France
            with: bus
            date: \'2018-05-09\'
        -
            to: Dublin, Ireland
            with: bus
            date: \'2018-05-10\'
',
        ]);

        $this->geocoder = $this->createMock(Geocoder::class);

        $this->parser = new BoucleParser($this->geocoder);
    }

    public function testItParsesAFullBoucle()
    {
        $location = $this->createMock(Address::class);
        $location->method('getCoordinates')->willReturn(new Coordinates(42.2, 24.4));

        $geocodingResults = new AddressCollection([$location]);
        $this->geocoder->expects($this->exactly(3))
            ->method('geocodeQuery')
            ->with($this->isInstanceOf(GeocodeQuery::class))
            ->willReturn($geocodingResults);

        $boucle = $this->parser->read($this->root->url().'/full_config_file.yaml');

        $this->assertInstanceOf(Boucle::class, $boucle);
        $this->assertSame('Europe – 2018', $boucle->title());
        $this->assertSame('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', $boucle->mapTileLayerUrl());
        $this->assertSame('key', $boucle->mapApiKey());

        $this->assertInstanceOf(Place::class, $boucle->startFrom());
        $this->assertSame(Transport::BUS, (string) $boucle->startBy());
        $this->assertSame('Clermont-Ferrand, France', $boucle->startFrom()->name());

        $this->assertCount(2, $boucle->steps());

        $this->assertSame('Lyon, France', $boucle->steps()[0]->to()->name());
        $this->assertSame('Dublin, Ireland', $boucle->steps()[1]->to()->name());
    }

    public function testItThrowsAnErrorIfTheStartIsNotSpecified(): void
    {
        $this->expectException(InvalidConfiguration::class);
        $this->expectExceptionMessage('The child node "start" at path "boucle" must be configured');

        $this->parser->read($this->root->url().'/invalid_no_start.yaml');
    }
}
