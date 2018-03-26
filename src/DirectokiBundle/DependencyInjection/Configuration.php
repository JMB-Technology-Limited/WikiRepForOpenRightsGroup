<?php

namespace DirectokiBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class Configuration implements ConfigurationInterface
{


    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('directoki');

        $rootNode
            ->children()
            ->booleanNode('read_only')->defaultValue(false)
            ->end()
        ;

        $rootNode
            ->children()
            ->booleanNode('collect_ip')->defaultValue(true)
            ->end()
        ;

        $rootNode
            ->children()
            ->booleanNode('collect_user_agent')->defaultValue(true)
            ->end()
        ;

        $rootNode
            ->children()
            ->integerNode('delete_information_after_hours')->defaultValue(4320)  // 24*30*6 = 6 months
            ->end()
        ;

        return $treeBuilder;
    }

}
