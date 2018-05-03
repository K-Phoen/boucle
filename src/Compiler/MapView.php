<?php

declare(strict_types=1);

namespace Boucle\Compiler;

use Boucle\Boucle;
use Symfony\Component\Filesystem\Filesystem;

class MapView
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var Filesystem */
    private $fs;

    public function __construct(\Twig_Environment $twig, Filesystem $fs = null)
    {
        $this->twig = $twig;
        $this->fs = $fs ?: new Filesystem();
    }

    public function compile(Boucle $boucle, string $webRoot): void
    {
        $html = $this->twig->render('map.html.twig', [
            'title' => $boucle->title(),
            'map_tile_layer_url' => $boucle->mapTileLayerUrl(),
            'map_api_key' => $boucle->mapApiKey(),
        ]);

        $this->fs->dumpFile($webRoot.'/index.html', $html);
    }
}
