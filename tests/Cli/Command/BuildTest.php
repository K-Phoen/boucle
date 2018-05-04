<?php

declare(strict_types=1);

namespace Tests\Boucle\Cli\Command;

use Boucle\Boucle;
use PHPUnit\Framework\TestCase;
use Boucle\Config\BoucleParser;
use Symfony\Component\Console\Output\OutputInterface;
use Boucle\Compiler;
use Boucle\Cli\Command\Build;

class BuildTest extends TestCase
{
    /** @var BoucleParser */
    private $boucleParser;

    /** @var Compiler\MapView */
    private $mapViewCompiler;

    /** @var Compiler\BoucleToJson */
    private $boucleToJsonCompiler;

    /** @var OutputInterface */
    private $output;

    /** @var Build */
    private $command;

    public function setUp(): void
    {
        $this->boucleParser = $this->createMock(BoucleParser::class);
        $this->mapViewCompiler = $this->createMock(Compiler\MapView::class);
        $this->boucleToJsonCompiler = $this->createMock(Compiler\BoucleToJson::class);
        $this->output = $this->createMock(OutputInterface::class);

        $this->command = new Build($this->boucleParser, $this->mapViewCompiler, $this->boucleToJsonCompiler);
    }

    public function testItDelegateTheWork(): void
    {
        $boucle = new Boucle('title', Boucle::MAP_MAPBOX, 'api-key', []);

        $this->boucleParser
            ->expects($this->once())
            ->method('read')
            ->with('boucle-path')
            ->willReturn($boucle);

        $this->mapViewCompiler
            ->expects($this->once())
            ->method('compile')
            ->with($boucle, 'web-root');

        $this->boucleToJsonCompiler
            ->expects($this->once())
            ->method('compile')
            ->with($boucle, 'web-root');

        $this->command->run($this->output, 'boucle-path', 'web-root');
    }
}
