<?php
namespace Cerad\Bundle\UserBundle\Doctrine\Entity;

class UserAuthen
{   
    protected $id;      // Unique oauth string
    protected $user;
    
    protected $provider;  // provider name
    protected $username;  // provider username
       
    public function getId()        { return $this->id;       }
    public function getUser()      { return $this->user;     }
    public function getProvider()  { return $this->provider; }
    public function getUsername()  { return $this->username; }
    
    public function __construct($provider,$username,$user)
    {
        $this->provider = $provider;
        $this->username = $username;
        $this->user     = $user;
    }
}
