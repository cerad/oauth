<?php

namespace Cerad\Bundle\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
//  Symfony\Component\DependencyInjection\Reference;

/* =======================================================
 * Addes a twig namespace pointing to the Action directory
 */
class Pass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $bundleDirAction = $container->getParameter('cerad_user__bundle_dir') . '/Action';
        
        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');
        
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', array($bundleDirAction, 'CeradUser'));        
    }
}