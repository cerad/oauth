<?php

namespace Cerad\Bundle\UserBundle\Doctrine\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Collections\ArrayCollection;

class User implements UserInterface,  \Serializable
{
    protected $id;
    protected $name;
    protected $username;
    
    protected $email;
    protected $emailVerified;
    
    protected $salt;
    protected $password;
    protected $passwordHint;
    protected $passwordPlain;
    
    protected $personKey;
    protected $personVerified;
    
    protected $roles = array('ROLE_USER');
    protected $authens;
    
    public function __construct($username)
    {
        $this->username = $username;
        $this->salt    = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->authens = new ArrayCollection;
    }
    public function getId()       { return $this->id; }
    public function getName()     { return $this->name; }
    public function getUsername() { return $this->username; }
    public function getEmail()    { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getSalt()     { return $this->salt; }
    
    public function setName    ($v) { $this->name     = $v; return $this; }
    public function setUsername($v) { $this->username = $v; return $this; }
    public function setEmail   ($v) { $this->email    = $v; return $this; }
    public function setPassword($v) { $this->password = $v; return $this; }
    
    // For importing
    public function setSalt    ($v) { $this->salt     = $v; return $this; }
    
    public function getRoles() 
    { 
        return $this->roles;
        
        if (in_array('ROLE_USER',$this->roles,true)) return $this->roles;
        
        return array_merge(array('ROLE_USER'),$this->roles);
    }
    public function eraseCredentials() 
    {
        $this->passwordPlain = null;
    }
    public function serialize()
    {
        return serialize(array(
            $this->id,         // For refreshing
            $this->salt,
            $this->password,
        ));
    }
   public function unserialize($serialized)
   {
        list(
            $this->id,
            $this->salt,
            $this->password,
        ) = unserialize($serialized);
   }
}