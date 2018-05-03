<?php

declare(strict_types=1);

namespace Boucle\Cli;

use Boucle\Config\BoucleParser;
use Boucle\Compiler;
use Geocoder;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Pimple\Container as Pimple;

class Container extends Pimple
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->configure();
    }

    private function configure(): void
    {
        $this['views_dir'] = dirname(__DIR__, 2).'/views/';
        $this['dist_dir'] = dirname(__DIR__, 2).'/dist/';

        $this->templating();
        $this->geocoder();
        $this->compilers();
        $this->commands();

        $this[BoucleParser::class] = function ($c) {
            return new BoucleParser($c[Geocoder\Geocoder::class]);
        };
    }

    private function geocoder(): void
    {
        $this[Geocoder\Geocoder::class] = function () {
            $httpClient = new GuzzleClient();
            $provider = Geocoder\Provider\Nominatim\Nominatim::withOpenStreetMapServer($httpClient);

            return new Geocoder\StatefulGeocoder($provider);
        };
    }

    private function templating(): void
    {
        $this['twig'] = function () {
            $loader = new \Twig_Loader_Filesystem($this['views_dir']);

            return new \Twig_Environment($loader, [
                'strict_variables' => true,
            ]);
        };
    }

    private function compilers(): void
    {
        $this[Compiler\MapView::class] = function ($c) {
            return new Compiler\MapView($c['twig']);
        };

        $this[Compiler\BoucleToJson::class] = function () {
            return new Compiler\BoucleToJson();
        };
    }

    private function commands(): void
    {
        $this[Command\ListSteps::class] = function ($c) {
            return new Command\ListSteps($c[BoucleParser::class]);
        };

        $this[Command\CopyDist::class] = function ($c) {
            return new Command\CopyDist($c['dist_dir']);
        };

        $this[Command\Build::class] = function ($c) {
            return new Command\Build(
                $c[BoucleParser::class],
                $c[Compiler\MapView::class],
                $c[Compiler\BoucleToJson::class]
            );
        };
    }
}
