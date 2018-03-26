<?php

namespace DirectokiBundle\DependencyInjection;


use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class DirectokiExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {

        $processedConfig = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter( 'directoki.read_only', $processedConfig[ 'read_only' ] );
        $container->setParameter( 'directoki.collect_ip', $processedConfig['collect_ip']);
        $container->setParameter( 'directoki.collect_user_agent', $processedConfig['collect_user_agent']);
        $container->setParameter( 'directoki.delete_information_after_hours', $processedConfig['delete_information_after_hours']);
    }

}
