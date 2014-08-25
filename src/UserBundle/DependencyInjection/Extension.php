<?php

namespace Cerad\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class Extension extends BaseExtension
{
    public function getAlias() { return 'cerad_user'; }
    
    public function load(array $configs, ContainerBuilder $container)
    {
      //$configuration = new Configuration();
      //$config = $this->processConfiguration($configuration, $configs);
        
        $config = array();
        foreach($configs as $configx)
        {
            $config = array_merge($config,$configx);
        }
        $providers = $config['oauth']['providers'];
        $container->setParameter('cerad_user__oauth__providers',$config['oauth']['providers']);
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $bundleDir = dirname(dirname(__FILE__));
        $container->setParameter('cerad_user__bundle_dir',$bundleDir);

    }
}
