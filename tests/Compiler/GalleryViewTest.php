<?php

declare(strict_types=1);

namespace Tests\Boucle\Compiler;

use Boucle\Album;
use Boucle\Boucle;
use Boucle\Compiler\GalleryView;
use Boucle\Coordinates;
use Boucle\Image;
use Boucle\Place;
use Boucle\Step;
use Boucle\Transport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class GalleryViewTest extends TestCase
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var Filesystem */
    private $fs;

    /** @var GalleryView */
    private $compiler;

    public function setUp(): void
    {
        $this->twig = $this->createMock(\Twig_Environment::class);
        $this->fs = $this->createMock(Filesystem::class);

        $this->compiler = new GalleryView($this->twig, $this->fs);
    }

    public function testItRendersTheView(): void
    {
        $boucle = new Boucle('Boucle title', Boucle::MAP_MAPBOX, 'api-key', []);
        $album = new Album('album-path', Image::fromPath('image-path'), []);
        $step = (new Step(
            new Place('somewhere', new Coordinates(0, 0)),
            new Place('somewhere else', new Coordinates(0, 0)),
            new \DateTimeImmutable(),
            Transport::PLANE()
        ))->withAlbum($album);

        $this->twig->expects($this->once())
            ->method('render')
            ->with('album.html.twig', [
                'title' => 'Boucle title',
                'step' => $step,
                'album' => $album,
                'webRoot' => '/webroot',
            ])
            ->willReturn('content');

        $this->fs->expects($this->once())
            ->method('dumpFile')
            ->with('album-path/index.html', 'content');

        $this->compiler->compile($boucle, $step, '/webroot');
    }
}
