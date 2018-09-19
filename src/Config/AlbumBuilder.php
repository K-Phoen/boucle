<?php

declare(strict_types=1);

namespace Boucle\Config;

use Boucle\Album;
use Boucle\Image;
use Boucle\Thumbnail\Thumbnailer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AlbumBuilder
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /** @var Thumbnailer */
    private $thumbnailer;

    public function __construct(Thumbnailer $thumbnailer)
    {
        $this->thumbnailer = $thumbnailer;
    }

    public function fromConfig(string $rootDir, array $config): Album
    {
        $albumPath = $rootDir.'/'.$config['path'];

        return new Album(
            $albumPath,
            $this->thumbnailer->forImage(Image::fromPath($albumPath.'/'.$config['cover'])),
            $this->imagesFromDir($albumPath)
        );
    }

    private function imagesFromDir(string $directory): iterable
    {
        $finder = Finder::create()
            ->files()
            ->filter(function (\SplFileInfo $fileInfo) {
                return \in_array(strtolower($fileInfo->getExtension()), self::ALLOWED_EXTENSIONS, true);
            })
            ->depth(0)
            ->sortByModifiedTime()
            ->in($directory);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            yield $this->thumbnailer->forImage(Image::fromPath($file->getPathname()));
        }
    }
}
