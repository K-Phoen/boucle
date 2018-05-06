<?php

declare(strict_types=1);

namespace Boucle\Compiler;

use Boucle\Boucle;
use Boucle\Place;
use Boucle\Transport;
use Symfony\Component\Filesystem\Filesystem;

class BoucleToJson
{
    /** @var Filesystem */
    private $fs;

    public function __construct(Filesystem $fs = null)
    {
        $this->fs = $fs ?: new Filesystem();
    }

    public function compile(Boucle $boucle, string $webRoot): void
    {
        $data = [
            'start' => [
                'from' => $this->compilePlace($boucle->startFrom()),
                'with' => (string) $boucle->startBy(),
            ],
            'steps' => array_fill_keys(Transport::consts(), []),
        ];

        foreach ($boucle->steps() as $step) {
            $data['steps'][(string) $step->transport()][] = [
                'from' => $this->compilePlace($step->from()),
                'to' => $this->compilePlace($step->to()),
                'path' => $step->pathFilename(),
                'date' => $step->date()->format('Y-m-d'),
            ];
        }

        $this->fs->dumpFile($webRoot.'/boucle.json', json_encode($data, JSON_PRETTY_PRINT));
    }

    private function compilePlace(Place $place): array
    {
        return [
            'name' => $place->name(),
            'lat' => $place->latitude(),
            'long' => $place->longitude(),
        ];
    }
}
