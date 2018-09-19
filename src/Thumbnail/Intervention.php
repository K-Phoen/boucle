<?php

declare(strict_types=1);

namespace Boucle\Thumbnail;

use Intervention\Image\Exception\ImageException;
use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;
use Boucle\Image;

class Intervention implements Thumbnailer
{
    /** @var ImageManager */
    private $manager;

    /** @var Filesystem */
    private $fs;

    public function __construct(ImageManager $manager = null, Filesystem $fs = null)
    {
        $this->manager = $manager ?: new ImageManager(['driver' => 'gd']);
        $this->fs = $fs ?: new Filesystem();
    }

    public function forImage(Image $image): Image
    {
        $thumb = Image::thumbFrom($image, 'boucle/thumb_');

        if ($this->fs->exists($thumb->thumbnailPath())) {
            return $thumb;
        }

        $this->fs->mkdir($thumb->thumbDirectory());

        try {
            $this->manager
                ->make($image->path())
                ->widen(400)
                ->save($thumb->thumbnailPath());
        } catch (ImageException $e) {
            throw new \RuntimeException('Could not create thumbnail for image: '.$image->path(), $e->getCode(), $e);
        }

        return $thumb;
    }
}
