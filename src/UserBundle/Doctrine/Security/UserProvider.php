<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cerad\Bundle\UserBundle\Doctrine\Security;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider implements 
  UserProviderInterface, 
  OAuthAwareUserProviderInterface, 
  AccountConnectorInterface
{
    protected $userRepo;
    protected $userClass;
    protected $userAuthenClass;
    
    public function __construct($userRepo)
    {
        $this->userRepo        = $userRepo;
        $this->userClass       = $userRepo->getClassName();
        $this->userAuthenClass = $this->userClass . 'Authen';
    }
    public function loadUserByUsername($username)
    {
        echo sprintf('loadUserByUsername %s<br />',$username);
        
        $user = $this->userRepo->findOneByUsername($username);
        if (!$user)
        {//die('loadUserByUsername ' . $username);
            throw new UsernameNotFoundException(sprintf("User '%s' not found.", $username));            
        }
        return $user;
    }
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {   
        $username = $response->getUsername();
        $provider = $response->getResourceOwner()->getName();
        
      //echo sprintf('loadUserByOAuthUserResponse %s %s<br />',$provider,$username);die();
        
        // Do this or have a findByProviderUsername
        $sql  = 'SELECT userId FROM userAuthens WHERE provider = :provider AND username = :username';
        $stmt = $this->userRepo->getConnection()->prepare($sql);
        $stmt->execute(array('provider' => $provider,'username' => $username));
        $rows = $stmt->fetchAll();
        if (count($rows) != 1)
        {
            throw new AccountNotLinkedException(sprintf("User '%s' '%s' not found.", $provider,$username));
        }
        $userId = (int)$rows[0]['userId'];
        $user = $this->userRepo->find($userId);
        
      //echo sprintf('loadedUserByOAuthUserResponse %s %d %s<br />',$username,$userId,$user->getUsername()); //die();
        
        if (!$user)
        {
            // Bad
            die('user notx found');
        }
        return $user;
      //return $this->loadUserByUsername($response->getUsername());  // Github 130533, Google 113055156735633728525 
      //return $this->loadUserByUsername($response->getRealname());  // Github blank
      //return $this->loadUserByUsername($response->getNickname());  // Github ahundiak
      //return $this->loadUserByUsername($response->getResourceOwner()->getName() . ' ' . $response->getUsername());  // Github ahundiak
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }
        return $this->userRepo->find($user->getId());
        
      //return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
      //die('supportsClass ' . $class . ' ' . $this->userClass);
        return $class === $this->userClass;
    }
    /* =================================================
     * Stash this here for now, probably shoud have it's own class
     */
    public function connect(UserInterface $user, UserResponseInterface $userInfo)
    {
        $username = $userInfo->getUsername();
        $provider = $userInfo->getResourceOwner()->getName();
        
        $userAuthen = new $this->userAuthenClass($provider,$username,$user);
        $this->userRepo->persist($user);
        $this->userRepo->persist($userAuthen);
        $this->userRepo->flush();
        
        return $user;
    }
}
