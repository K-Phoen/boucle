<?php

declare(strict_types=1);

namespace Tests\Boucle\Compiler;

use Boucle\Boucle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Boucle\Compiler\MapView;

class MapViewTest extends TestCase
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var Filesystem */
    private $fs;

    /** @var MapView */
    private $compiler;

    public function setUp(): void
    {
        $this->twig = $this->createMock(\Twig_Environment::class);
        $this->fs = $this->createMock(Filesystem::class);

        $this->compiler = new MapView($this->twig, $this->fs);
    }

    public function testItRendersTheView(): void
    {
        $boucle = new Boucle('Boucle title', Boucle::MAP_MAPBOX, 'api-key', []);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('map.html.twig', [
                'map_tile_layer_url' => $boucle->mapTileLayerUrl(),
                'map_api_key' => 'api-key',
                'title' => 'Boucle title',
            ])
            ->willReturn('content');

        $this->fs->expects($this->once())
            ->method('dumpFile')
            ->with('/webroot/index.html', 'content');

        $this->compiler->compile($boucle, '/webroot');
    }
}
