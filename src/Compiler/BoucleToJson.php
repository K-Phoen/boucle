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
                'departure_date' => $boucle->start()->date()->format('Y-m-d'),
                'with' => (string) $boucle->startBy(),
            ],
            'end' => [
                'arrival_date' => $boucle->end()->date()->format('Y-m-d'),
            ],
            'steps' => array_fill_keys(Transport::consts(), []),
        ];

        foreach ($boucle->steps() as $i => $step) {
            if (!$boucle->hasStep($i + 1)) {
                $departureDate = '';
            } else {
                $departureDate = $boucle->step($i + 1)->date()->format('Y-m-d');
            }

            $albumData = null;

            if ($step->hasAlbum()) {
                $album = $step->album();

                $albumData = [
                    'path' => $album->pathRelativeTo($webRoot),
                    'cover' => $album->cover()->relativeTo($webRoot)->thumbnailPath(),
                ];
            }

            $data['steps'][(string) $step->transport()][] = [
                'from' => $this->compilePlace($step->from()),
                'to' => $this->compilePlace($step->to()),
                'path' => $step->pathFilename(),
                'arrival_date' => $step->date()->format('Y-m-d'),
                'departure_date' => $departureDate,
                'album' => $albumData,
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
