<?php

declare(strict_types=1);

namespace Boucle\Cli\Command;

use Boucle\Config\BoucleParser;
use Symfony\Component\Console\Output\OutputInterface;
use Boucle\Compiler;

class Build
{
    /** @var BoucleParser */
    private $boucleParser;

    /** @var Compiler\MapView */
    private $mapViewCompiler;

    /** @var Compiler\BoucleToJson */
    private $jsonCompiler;

    public function __construct(BoucleParser $boucleParser, Compiler\MapView $mapViewCompiler, Compiler\BoucleToJson $jsonCompiler)
    {
        $this->boucleParser = $boucleParser;
        $this->mapViewCompiler = $mapViewCompiler;
        $this->jsonCompiler = $jsonCompiler;
    }

    public function run(OutputInterface $output, string $bouclePath, string $webRoot): void
    {
        $output->writeln('<info>Analysing boucle...</info>');

        $boucle = $this->boucleParser->read($bouclePath);

        $output->writeln('<info>Compiling map view...</info>');

        $this->mapViewCompiler->compile($boucle, $webRoot);

        $output->writeln('<info>Compiling boucle to json...</info>');

        $this->jsonCompiler->compile($boucle, $webRoot);
    }
}
