<?php

declare(strict_types=1);

namespace Tests\Boucle;

use PHPUnit\Framework\TestCase;
use Boucle\Album;
use Boucle\Image;

class AlbumTest extends TestCase
{
    public function testItRepresentsAnAlbum(): void
    {
        $albumPath = '/webroot/data/album-name';
        $cover = Image::fromPath($albumPath.'/cover.jpeg');
        $images = [$cover];
        $album = new Album($albumPath, $cover, $images);

        $this->assertSame($albumPath, $album->path());
        $this->assertSame('data/album-name', $album->pathRelativeTo('/webroot/'));
        $this->assertSame($images, $album->images());
        $this->assertSame($cover, $album->cover());
    }
}
