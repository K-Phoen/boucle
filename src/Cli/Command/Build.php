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

    /** @var Compiler\GalleryView */
    private $galleryViewCompiler;

    /** @var Compiler\BoucleToJson */
    private $jsonCompiler;

    public function __construct(BoucleParser $boucleParser, Compiler\MapView $mapViewCompiler, Compiler\GalleryView $galleryViewCompiler, Compiler\BoucleToJson $jsonCompiler)
    {
        $this->boucleParser = $boucleParser;
        $this->mapViewCompiler = $mapViewCompiler;
        $this->jsonCompiler = $jsonCompiler;
        $this->galleryViewCompiler = $galleryViewCompiler;
    }

    public function run(OutputInterface $output, string $bouclePath, string $webRoot): void
    {
        $output->writeln('<info>Analysing boucle...</info>');

        $boucle = $this->boucleParser->read($bouclePath);

        $output->writeln('<info>Compiling map view...</info>');

        $this->mapViewCompiler->compile($boucle, \realpath($webRoot));

        $output->writeln('<info>Compiling boucle to json...</info>');

        $this->jsonCompiler->compile($boucle, \realpath($webRoot));

        $output->writeln('<info>Compiling galleries...</info>');

        foreach ($boucle->steps() as $step) {
            if (!$step->hasAlbum()) {
                continue;
            }

            $this->galleryViewCompiler->compile($boucle, $step, $webRoot);
        }
    }
}
