<?php

declare(strict_types=1);

namespace Boucle\Cli\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CopyDist
{
    /** @var string */
    private $distDir;

    /** @var Filesystem */
    private $fs;

    public function __construct(string $distDir, Filesystem $fs = null)
    {
        $this->distDir = $distDir;
        $this->fs = $fs ?: new Filesystem();
    }

    public function run(OutputInterface $output, string $webRoot): void
    {
        $output->writeln('<info>Copying compiled CSS and JS assets...</info>');

        $this->fs->mirror($this->distDir, \realpath($webRoot).'/dist');
    }
}
