<?php

declare(strict_types=1);

namespace Boucle;

final class Boucle
{
    public const MAP_OPENSTREETMAP = 'openstreetmap';

    public const MAP_MAPBOX = 'mapbox';

    /** @var string */
    private $title;

    /** @var string */
    private $mapProvider;

    /** @var string */
    private $mapApiKey;

    /** @var Step[] */
    private $steps;

    public function __construct(string $title, string $mapProvider, string $mapApiKey, array $steps)
    {
        if (!\in_array($mapProvider, [self::MAP_OPENSTREETMAP, self::MAP_MAPBOX], true)) {
            throw new \DomainException('Invalid map provider option');
        }

        if ($mapProvider === self::MAP_MAPBOX && empty($mapApiKey)) {
            throw new \DomainException('The chosen map provider requires an API key');
        }

        $this->title = $title;
        $this->mapProvider = $mapProvider;
        $this->mapApiKey = $mapApiKey;
        $this->steps = $steps;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function start(): Step
    {
        if (empty($this->steps)) {
            throw new \LogicException('Could not get the start of the boucle: no steps registered');
        }

        return $this->steps[0];
    }

    public function end(): Step
    {
        if (empty($this->steps)) {
            throw new \LogicException('Could not get the end of the boucle: no steps registered');
        }

        return $this->steps[count($this->steps) - 1];
    }

    public function startBy(): Transport
    {
        return $this->start()->transport();
    }

    public function startFrom(): Place
    {
        return $this->start()->from();
    }

    public function mapTileLayerUrl(): string
    {
        switch ($this->mapProvider) {
            case self::MAP_MAPBOX:
                return 'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}';
            case self::MAP_OPENSTREETMAP:
                return 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            default:
                throw new \LogicException('No tile layer URL found.');
        }
    }

    public function mapApiKey(): string
    {
        return $this->mapApiKey;
    }

    /**
     * @return iterable|Step[]
     */
    public function steps(): iterable
    {
        return $this->steps;
    }

    /**
     * SMELL -> if steps were stored as a (doubly) linked list, we
     * could easily access the previous and next steps and we would
     * not have to rely on these hacky numeric indices.
     *
     * @TODO refactor
     */
    public function hasStep(int $i): bool
    {
        return isset($this->steps[$i]);
    }

    public function step(int $i): Step
    {
        if (!$this->hasStep($i)) {
            throw new \LogicException(sprintf('No step at index "%d"', $i));
        }

        return $this->steps[$i];
    }
}
