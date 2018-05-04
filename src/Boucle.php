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

    public function startBy(): Transport
    {
        if (empty($this->steps)) {
            throw new \LogicException('Could not get the start of the boucle: no steps registered');
        }

        return $this->steps[0]->transport();
    }

    public function startFrom(): Place
    {
        if (empty($this->steps)) {
            throw new \LogicException('Could not get the start of the boucle: no steps registered');
        }

        return $this->steps[0]->from();
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
}
