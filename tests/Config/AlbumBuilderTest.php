<?php

declare(strict_types=1);

namespace Tests\Boucle\Config;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Boucle\Config\AlbumBuilder;
use Boucle\Image;
use Boucle\Thumbnail\Thumbnailer;

class AlbumBuilderTest extends TestCase
{
    /** @var Thumbnailer */
    private $thumbnailer;

    /** @var vfsStreamDirectory */
    private $root;

    /** @var AlbumBuilder */
    private $albumBuilder;

    public function setUp()
    {
        $this->thumbnailer = $this->createMock(Thumbnailer::class);

        $this->root = vfsStream::setup('root_dir', null, [
            'album' => [
                'some-text-file.txt' => 'content',
                '201805101439_001.jpg' => 'content',
                '201805101439_002.PNG' => 'content',
            ],
        ]);

        $this->albumBuilder = new AlbumBuilder($this->thumbnailer);
    }

    public function testItCreateAnAlbumFromConfig()
    {
        $thumb = Image::fromPath('irrelevant');

        $this->thumbnailer->expects($this->exactly(3))->method('forImage')->willReturn($thumb);

        $album = $this->albumBuilder->fromConfig($this->root->url(), [
            'path' => 'album',
            'cover' => '201805101439_001.jpg',
        ]);

        $images = iterator_to_array($album->images());

        $this->assertCount(2, $images);
    }
}
