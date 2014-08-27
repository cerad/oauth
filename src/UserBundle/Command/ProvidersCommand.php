<?php

namespace Cerad\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ProvidersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_user__oauth__providers')
            ->setDescription('Debug Providers')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo "OAuth Providers\n";
        
        $providerManager = $this->getContainer()->get('cerad_user__oauth__provider_manager');
        
        $provider1 = $providerManager->createFromName('google');
        
        echo sprintf("Provider1 name %s\n",$provider1->getName());
        
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        
        $authUrl = $provider1->getAuthorizationUrl($request);
        
        $state = $session->get('cerad_user__oauth__state');
        
        echo $state . "\n";
        
        echo $authUrl . "\n";
        
        // Pretend we redirected
        $request->query->set('code', 'the_code');;
        $request->query->set('state',$state);
        
        $provider2 = $providerManager->createFromRequest($request);
        
        echo "\n";
        echo sprintf("Provider2 name %s\n",$provider2->getName());
    }
}
