<?php

declare(strict_types=1);

namespace Boucle\Config;

use Boucle\Boucle;
use Boucle\Place;
use Boucle\Step;
use Boucle\Transport;
use Geocoder\Geocoder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Exception\CollectionIsEmpty as NoGeocodingResult;
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

        $steps = $this->readSteps($config);

        return new Boucle(
            $config['title'],
            $config['map_provider'],
            $config['map_api_key'],
            $steps
        );
    }

    /**
     * @return iterable|Step[]
     */
    private function readSteps(array $config): iterable
    {
        $steps = [];
        $start = new Place($config['start'], $this->geocode($config['start']));

        foreach ($config['steps'] as $i => $stepConfig) {
            $steps[] = $this->buildStep($stepConfig, $i === 0 ? $start : $steps[$i - 1]->to());
        }

        return $steps;
    }

    private function buildStep(array $stepConfig, Place $previous): Step
    {
        return new Step(
            $previous,
            new Place($stepConfig['to'], $this->geocode($stepConfig['to'])),
            \DateTimeImmutable::createFromFormat('Y-m-d', $stepConfig['date']),
            new Transport($stepConfig['with']),
            $stepConfig['path']
        );
    }

    private function geocode(string $location): Coordinates
    {
        $result = $this->geocoder->geocodeQuery(GeocodeQuery::create($location));

        try {
            return new Coordinates(
                $result->first()->getCoordinates()->getLatitude(),
                $result->first()->getCoordinates()->getLongitude()
            );
        } catch (NoGeocodingResult $e) {
            throw UnknownLocation::fromName($location, $e);
        }
    }
}
