<?php

namespace Cerad\Bundle\UserBundle\OAuth;

class Providers
{
    protected $map;
    
    public function __construct($providers)
    {
        foreach($providers as $provider) {
            $provider['instance'] = null;
            $this->map[$provider['name']] = $provider;
        }
    }
    public function create($name)
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
        $instance = new $class($info['name'],$info['client_id'],$info['client_secret']);
        
        $this->map[$name]['instance'] = $instance;
        return $instance;
    }
    public function getProviders() { return $this->map; }
}