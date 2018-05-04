<?php

declare(strict_types=1);

namespace Tests\Boucle\Cli;

use PHPUnit\Framework\TestCase;
use Boucle\Cli\Container;

class ContainerTest extends TestCase
{
    public function testItCanBeConstructed(): void
    {
        $container = new Container();

        $this->assertNotEmpty($container['views_dir']);
    }
}
