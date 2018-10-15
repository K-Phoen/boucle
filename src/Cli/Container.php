<?php

declare(strict_types=1);

namespace Boucle\Cli;

use Boucle\Config\AlbumBuilder;
use Boucle\Config\BoucleParser;
use Boucle\Compiler;
use Boucle\Thumbnail\Intervention;
use Geocoder;
use Http\Adapter\Guzzle6\Client as GuzzleClient;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\StreamFactoryDiscovery;
use Pimple\Container as Pimple;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;

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
        $this['cache_dir'] = (new \XdgBaseDir\Xdg())->getHomeCacheDir().'/boucle/';

        $this->templating();
        $this->geocoder();
        $this->compilers();
        $this->commands();

        $this[AlbumBuilder::class] = function () {
            return new AlbumBuilder(new Intervention());
        };

        $this[BoucleParser::class] = function ($c) {
            return new BoucleParser($c[Geocoder\Geocoder::class], $c[AlbumBuilder::class]);
        };
    }

    private function geocoder(): void
    {
        $this[Geocoder\Geocoder::class] = function () {
            $cache = new FilesystemAdapter('', 0, $this['cache_dir'].'/geocoding');
            $cachePlugin = new CachePlugin($cache, StreamFactoryDiscovery::find(), [
                'respect_cache_headers' => false,
                'default_ttl' => null,
                'cache_lifetime' => 86400 * 365,
            ]);
            $httpClient = new PluginClient(new GuzzleClient(), [$cachePlugin]);

            $provider = Geocoder\Provider\Nominatim\Nominatim::withOpenStreetMapServer($httpClient);

            return new Geocoder\StatefulGeocoder($provider);
        };
    }

    private function templating(): void
    {
        $this['twig'] = function () {
            $loader = new \Twig_Loader_Filesystem($this['views_dir']);

            $environment = new \Twig_Environment($loader, [
                'strict_variables' => true,
            ]);

            $environment->addFunction(new \Twig_Function('relative_path_between', function (string $path, string $root): string {
                $fs = new Filesystem();

                return rtrim($fs->makePathRelative(\realpath($root), \realpath($path)), '/');
            }));

            return $environment;
        };
    }

    private function compilers(): void
    {
        $this[Compiler\MapView::class] = function ($c) {
            return new Compiler\MapView($c['twig']);
        };

        $this[Compiler\GalleryView::class] = function ($c) {
            return new Compiler\GalleryView($c['twig']);
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
                $c[Compiler\GalleryView::class],
                $c[Compiler\BoucleToJson::class]
            );
        };
    }
}
