<?php

declare(strict_types=1);

namespace Boucle\Cli;

use Boucle\Config\BoucleConfiguration;
use Silly\Application as Silly;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimple\Psr11\Container as Psr11Container;

class Application extends Silly
{
    private const DEFAULT_BOUCLE = 'boucle.yaml';

    public function __construct(\Pimple\Container $container)
    {
        parent::__construct('Boucle');

        $this->useContainer(new Psr11Container($container));
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->registerCommands();

        return parent::run($input, $output);
    }

    private function registerCommands(): void
    {
        $this
            ->command('config:dump-reference', function (OutputInterface $output): void {
                $dumper = new YamlReferenceDumper();

                $output->writeln($dumper->dump(new BoucleConfiguration()));
            })
            ->descriptions('Dumps the default configuration for a boucle.');

        $this
            ->command('steps:list [boucle]', function (string $boucle = self::DEFAULT_BOUCLE, OutputInterface $output): void {
                $this->service(Command\ListSteps::class)->run($output, $boucle);
            })
            ->descriptions('List the steps in a given boucle.');

        $this
            ->command('build [boucle] [webroot]', function (string $boucle = self::DEFAULT_BOUCLE, string $webroot = '.', OutputInterface $output): void {
                if (!is_dir($webroot)) {
                    throw new \RuntimeException("Directory '$webroot' does not exist.");
                }

                $this->service(Command\Build::class)->run($output, $boucle, $webroot);
                $this->service(Command\CopyDist::class)->run($output, $webroot);

                $output->writeln('Done.');
            })
            ->descriptions('Builds the map and galleries.');
    }

    private function service(string $service)
    {
        return $this->getContainer()->get($service);
    }
}
