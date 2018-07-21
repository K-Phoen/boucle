<?php

declare(strict_types=1);

namespace Boucle\Cli\Command;

use Boucle\Config\BoucleParser;
use Boucle\Place;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ListSteps
{
    /** @var BoucleParser */
    private $parser;

    public function __construct(BoucleParser $boucleParser)
    {
        $this->parser = $boucleParser;
    }

    public function run(OutputInterface $output, string $bouclePath): void
    {
        $boucle = $this->parser->read($bouclePath);

        $table = new Table($output);
        $table->setHeaders(['From', 'To', 'Date', 'With']);

        foreach ($boucle->steps() as $step) {
            $table->addRow([
                $this->placeToString($output, $step->from()),
                $this->placeToString($output, $step->to()),
                $step->date()->format('Y-m-d'),
                $step->transport()->getValue(),
            ]);
        }

        $table->render();
    }

    private function placeToString(OutputInterface $output, Place $place): string
    {
        if ($output->isVerbose()) {
            return sprintf('%s (%f %f)', $place->name(), $place->latitude(), $place->longitude());
        }

        return $place->name();
    }
}
