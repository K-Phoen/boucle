<?php

declare(strict_types=1);

namespace Tests\Boucle;

use Boucle\Transport;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    public function testItCanListItsConstants(): void
    {
        $this->assertSame(['bus', 'car', 'plane', 'boat', 'train', 'walking'], Transport::consts());
    }
}
