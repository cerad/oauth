<?php

namespace Cerad\Bundle\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Cerad\Bundle\UserBundle\DependencyInjection\Extension;
use Cerad\Bundle\UserBundle\DependencyInjection\Compiler\Pass;

class CeradUserBundle extends Bundle
{
    public function getContainerExtension()
    {
        return $this->extension = new Extension();
    }
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Pass());
    }
}
