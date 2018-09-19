<?php

declare(strict_types=1);

namespace Tests\Boucle\Thumbnail;

use Intervention\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Boucle\Image;
use Boucle\Thumbnail\Intervention;
use Intervention\Image\Exception\ImageException;

class InterventionTest extends TestCase
{
    /** @var Filesystem */
    private $fs;

    /** @var ImageManager */
    private $manager;

    /** @var Intervention */
    private $thumbnailer;

    public function setUp(): void
    {
        $this->manager = $this->createMock(ImageManager::class);
        $this->fs = $this->createMock(Filesystem::class);

        $this->thumbnailer = new Intervention($this->manager, $this->fs);
    }

    public function testItDoesNothingIfTheThumbnailAlreadyExists(): void
    {
        $image = Image::fromPath('/dir/img.png');

        $this->fs
            ->method('exists')
            ->with('/dir/boucle/thumb_img.png')
            ->willReturn(true);

        $this->manager->expects($this->never())->method('make');
        $this->fs->expects($this->never())->method('mkdir');

        $thumb = $this->thumbnailer->forImage($image);

        $this->assertSame('/dir/boucle/thumb_img.png', $thumb->thumbnailPath());
        $this->assertSame('/dir/img.png', $thumb->path());
    }

    public function testItCreateItIfTheThumbnailDoesNotExist(): void
    {
        $image = Image::fromPath('/dir/img.png');

        $this->fs
            ->method('exists')
            ->with('/dir/boucle/thumb_img.png')
            ->willReturn(false);

        $interventionImage = $this->getMockBuilder(\Intervention\Image\Image::class)
            ->setMethods(['widen', 'save'])
            ->getMock();
        $interventionImage->method('widen')->willReturnSelf();
        $interventionImage->expects($this->once())
            ->method('save')
            ->with('/dir/boucle/thumb_img.png');

        $this->manager->expects($this->once())
            ->method('make')
            ->with($image->path())
            ->willReturn($interventionImage);

        $this->fs->expects($this->once())
            ->method('mkdir')
            ->with('/dir/boucle');

        $this->thumbnailer->forImage($image);
    }

    public function testItWrapsErrors(): void
    {
        $image = Image::fromPath('/dir/img.png');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not create thumbnail for image: /dir/img.png');

        $this->manager->method('make')->willThrowException(new ImageException());

        $this->thumbnailer->forImage($image);
    }
}
