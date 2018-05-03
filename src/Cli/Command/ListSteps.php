<?php

declare(strict_types=1);

namespace Boucle\Cli\Command;

use Boucle\Config\BoucleParser;
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
                sprintf('%s (%f %f)', $step->from()->name(), $step->from()->latitude(), $step->from()->longitude()),
                sprintf('%s (%f %f)', $step->to()->name(), $step->to()->latitude(), $step->to()->longitude()),
                $step->date()->format('Y-m-d'),
                $step->transport()->getValue(),
            ]);
        }

        $table->render();
    }
}
