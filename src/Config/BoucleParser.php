<?php

declare(strict_types=1);

namespace Boucle\Config;

use Boucle\Boucle;
use Boucle\Place;
use Boucle\Step;
use Boucle\StepType;
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
        $start = new Place($config['start'], $this->geocode($config['start']));
        $startStep = new Step($start, $start, new \DateTimeImmutable(), Transport::BUS());
        $previousStepConfig = [];
        $steps = [];

        foreach ($config['steps'] as $stepConfig) {
            $previousStep = empty($steps) ? $startStep : end($steps);

            foreach ($this->buildStep($stepConfig, $previousStep, $previousStepConfig) as $step) {
                $steps[] = $step;
            }

            $previousStepConfig = $stepConfig;
        }

        return $steps;
    }

    /**
     * @return Step[]
     */
    private function buildStep(array $stepConfig, Step $previousStep, array $previousStepConfig): \Generator
    {
        $coordinates = $this->coordinatesForStepConfig($stepConfig);
        $to = new Place($stepConfig['to'], $coordinates);
        $date = $this->dateForStepConfig($stepConfig, $previousStep, $previousStepConfig);
        $with = new Transport($stepConfig['with']);

        yield new Step($previousStep->to(), $to, $date, $with, $stepConfig['path']);

        // the current step is a daytrip so we "artificially" create the return step
        if ($stepConfig['type'] === StepType::DAY_TRIP) {
            yield new Step($to, $previousStep->to(), $date, $with);
        }
    }

    private function dateForStepConfig(array $stepConfig, Step $previousStep, array $previousStepConfig): \DateTimeImmutable
    {
        $stepType = new StepType($stepConfig['type']);

        if (!empty($previousStepConfig['duration']) && !empty($stepConfig['date']) && !StepType::isSingleDay($stepType)) {
            throw new InvalidConfiguration('Both duration and date were given for the step to '.$stepConfig['to']);
        }

        if (!empty($stepConfig['date'])) {
            if ($this->isDateRelative($stepConfig['date'])) {
                return $this->parseRelativeDate($previousStep->date(), $stepConfig['date']);
            }

            return \DateTimeImmutable::createFromFormat('Y-m-d', $stepConfig['date']);
        }

        return $previousStep->date()->modify('+ '.$previousStepConfig['duration']);
    }

    private function parseRelativeDate(\DateTimeImmutable $base, string $date): \DateTimeImmutable
    {
        $modifier = str_replace('previous + ', '', $date);

        return $base->modify('+'.$modifier);
    }

    private function isDateRelative(string $date): bool
    {
        return strpos($date, 'previous + ') === 0;
    }

    private function coordinatesForStepConfig(array $stepConfig): Coordinates
    {
        if (!empty($stepConfig['latitude']) && !empty($stepConfig['longitude'])) {
            return new Coordinates(
                $stepConfig['latitude'],
                $stepConfig['longitude']
            );
        }

        return $this->geocode($stepConfig['to']);
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
