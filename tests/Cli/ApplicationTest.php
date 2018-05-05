<?php

declare(strict_types=1);

namespace Tests\Boucle\Cli;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Boucle\Cli\Application;
use Boucle\Cli\Container;

class ApplicationTest extends TestCase
{
    /** @var Container */
    private $container;

    /** @var Container */
    private $application;

    public function setUp(): void
    {
        $this->container = new Container();

        $this->application = new Application($this->container);
        $this->application->setAutoExit(false);
    }

    public function testItCanBeConstructed(): void
    {
        $this->assertInstanceOf(Application::class, $this->application);
    }

    public function testItCanRun(): void
    {
        $output = $this->createMock(OutputInterface::class);

        $this->application->run(null, $output);

        $this->assertTrue($this->application->has('steps:list'));
        $this->assertTrue($this->application->has('build'));
    }

    public function testItCanDumpItsReference(): void
    {
        $this->application->run(new ArgvInput(['app_name', 'config:dump-reference']), new NullOutput());

        $this->assertTrue(true);
    }
}
