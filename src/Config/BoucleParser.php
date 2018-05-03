<?php

declare(strict_types=1);

namespace Boucle\Config;

use Boucle\Boucle;
use Boucle\Place;
use Boucle\Step;
use Boucle\Transport;
use Geocoder\Geocoder;
use Geocoder\Query\GeocodeQuery;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Boucle\Coordinates;

class BoucleParser
{
    /** @var Geocoder */
    private $geocoder;

    public function __construct(Geocoder $geocoder)
    {
        $this->geocoder = $geocoder;
    }

    public function read(string $path): Boucle
    {
        $processor = new Processor();

        try {
            $config = $processor->processConfiguration(new BoucleConfiguration(), Yaml::parseFile($path));
        } catch (InvalidConfigurationException $e) {
            throw InvalidConfiguration::inFile($path, $e->getMessage(), $e);
        }

        return new Boucle(
            $config['title'],
            $config['map_provider'],
            $config['map_api_key'],
            array_map([$this, 'buildStep'], $config['steps'])
        );
    }

    private function buildStep(array $stepConfig): Step
    {
        return new Step(
            new Place($stepConfig['from'], $this->geocode($stepConfig['from'])),
            new Place($stepConfig['to'], $this->geocode($stepConfig['to'])),
            \DateTimeImmutable::createFromFormat('Y-m-d', $stepConfig['date']),
            new Transport($stepConfig['with'])
        );
    }

    private function geocode(string $location): Coordinates
    {
        $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($location));

        return new Coordinates(
            $result->first()->getCoordinates()->getLatitude(),
            $result->first()->getCoordinates()->getLongitude()
        );
    }
}
