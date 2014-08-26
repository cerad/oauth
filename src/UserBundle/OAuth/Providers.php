<?php

namespace Cerad\Bundle\UserBundle\OAuth;

use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;


class Providers
{
    protected $map;
    protected $httpUtils;
    protected $redirectUriName = 'cerad_user__oauth__redirect';
    
    public function __construct(HttpUtils $httpUtils,$providers)
    {
        $this->httpUtils = $httpUtils;
        
        foreach($providers as $provider) {
            $provider['instance'] = null;
            $this->map[$provider['name']] = $provider;
        }
    }
    public function getProviders() { return $this->map; }

    public function createFromName($name)
    {
        if (!isset($this->map[$name])) {
            throw new \Exception(sprintf("Cerad User Oauth Provider not found: %s",$name));
        }
        if (isset( $this->map[$name]['instance'])) { 
            return $this->map[$name]['instance']; 
        }
        // Create it
        $info = $this->map[$name];
        $class = $info['class'];
        
        $instance = new $class($this,$info['name'],$info['client_id'],$info['client_secret']);
        
        $this->map[$name]['instance'] = $instance;
        
        return $instance;
    }
    // Process a redirection from the provider site
    public function createFromRequest(Request $request)
    {
        $requestState = $request->query->get('state');
        $storageState = $request->getSession()->get('cerad_user__oauth__state');
        if ($requestState != $storageState) {
            throw new \Exception("OAuth State Mismatch");
        }
        $parts = explode(':',$requestState);
        if (count($parts) != 2) {
            throw new \Exception("OAuth State Invalid Format");
        }
        return $this->createFromName($parts[0]);
    }
    public function getRedirectUri(Request $request)
    {
        // http://local.oauth.zayso.org/oauth/callback
        return $this->httpUtils->generateUri($request,$this->redirectUriName);
    }
    public function generateState(Request $request, $name)
    {
        $state = $name . ':' . md5(microtime(true).uniqid('', true));
        
        if ($request->hasSession())
        {
            // This should probably be some sort of storage
            $session = $request->getSession();
            $session->set('cerad_user__oauth__state',$state);   
        }
        return $state;
    }
}