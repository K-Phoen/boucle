<?php

declare(strict_types=1);

namespace Boucle\Config;

use Boucle\Boucle;
use Boucle\Transport;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class BoucleConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('boucle');

        $rootNode
            ->children()
                ->scalarNode('title')->cannotBeEmpty()->defaultValue('Boucle')->end()

                ->enumNode('map_provider')
                    ->values([Boucle::MAP_OPENSTREETMAP, Boucle::MAP_MAPBOX])
                    ->cannotBeEmpty()
                    ->defaultValue(Boucle::MAP_OPENSTREETMAP)
                ->end()
                ->scalarNode('map_api_key')->cannotBeEmpty()->defaultValue('')->end()

                ->scalarNode('start')->isRequired()->cannotBeEmpty()->end()

                ->arrayNode('steps')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('to')->isRequired()->cannotBeEmpty()->end()
                            ->floatNode('latitude')->defaultNull()->end()
                            ->floatNode('longitude')->defaultNull()->end()
                            ->scalarNode('path')
                                ->info('GPX file describing the path, relative the the web directory.')
                                ->cannotBeEmpty()
                                ->defaultValue('')
                            ->end()
                            ->scalarNode('date')->isRequired()->cannotBeEmpty()->end()
                            ->enumNode('with')
                                ->values(Transport::consts())
                                ->cannotBeEmpty()
                                ->defaultValue(Transport::PLANE)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
