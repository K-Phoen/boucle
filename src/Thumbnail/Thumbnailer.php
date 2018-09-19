<?php

declare(strict_types=1);

namespace Boucle\Thumbnail;

use Boucle\Image;

interface Thumbnailer
{
    public function forImage(Image $image): Image;
}
