<?php

declare(strict_types=1);

namespace Boucle\Compiler;

use Boucle\Boucle;
use Boucle\Step;
use Symfony\Component\Filesystem\Filesystem;

class GalleryView
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

    public function compile(Boucle $boucle, Step $step, string $webRoot): void
    {
        $album = $step->album();

        $html = $this->twig->render('album.html.twig', [
            'title' => $boucle->title(),
            'step' => $step,
            'album' => $album,
            'webRoot' => $webRoot,
        ]);

        $this->fs->dumpFile($album->path().'/index.html', $html);
    }
}
