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
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * OAuthUserProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    protected $userRepo;
    protected $userClass;
    
    public function __construct($userRepo)
    {
        $this->userRepo  = $userRepo;
        $this->userClass = $userRepo->getClassName();
    }
    public function loadUserByUsername($username)
    {
        return new $this->userClass($username);
    }
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {   
        $provider = $response->getResourceOwner()->getName();
        $username = $response->getUsername();
                    
        $sql  = 'SELECT userId FROM userAuthens WHERE provider = :provider AND username = :username';
        $stmt = $this->userRepo->getConnection()->prepare($sql);
        $stmt->execute(array('provider' => $provider,'username' => $username));
        $rows = $stmt->fetchAll();
        if (count($rows) != 1)
        {
            throw new UsernameNotFoundException(sprintf("User '%s' '%s' not found.", $provider,$username));
        }
        $userId = (int)$rows[0]['userId'];
        $user = $this->userRepo->find($userId);
        
        if (!$user)
        {
            // Bad
            die('user not found');
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
        return $user;
        
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
      //die('supportsClass ' . $class . ' ' . $this->userClass);
        return $class === $this->userClass;
    }
}
