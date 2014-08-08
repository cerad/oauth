<?php

namespace Cerad\Bundle\UserBundle\Action\Registration;

use FOS\UserBundle\Form\Handler\RegistrationFormHandler;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGenerator;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Form\RegistrationFormHandlerInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class RegFormHandler implements RegistrationFormHandlerInterface
{
    protected $userRepo;
    protected $userClass;
    
    public function __construct($userRepo)
    {
        $this->userRepo  = $userRepo;
        $this->userClass = $userRepo->getClassName();
    }
    
    public function process(Request $request, Form $form, UserResponseInterface $userInformation)
    {
        $name     = $userInformation->getRealname() ? $userInformation->getRealname() : $userInformation->getNickname();
        $email    = $userInformation->getEmail();
        $username = $userInformation->getResourceOwner()->getName() . '-' . $userInformation->getUsername();
        
        $user = new $this->userClass($username,$email,$name);

        $form->setData($user);
        
        $form->handleRequest($request);
        
        return $form->isValid();
    }

    /**
     * Attempts to get a unique username for the user.
     *
     * @param string $name
     *
     * @return string Name, or empty string if it failed after all the iterations.
     */
    protected function getUniqueUserName($name)
    {
        $i = 0;
        $testName = $name;

        do {
            $user = $this->userManager->findUserByUsername($testName);
        } while ($user !== null && $i < $this->iterations && $testName = $name.++$i);

        return $user !== null ? '' : $testName;
    }

    /**
     * Set user information from form
     *
     * @param UserInterface         $user
     * @param UserResponseInterface $userInformation
     *
     * @return UserInterface
     */
    protected function setUserInformation(UserInterface $user, UserResponseInterface $userInformation)
    {
        $user->setUsername($this->getUniqueUsername($userInformation->getNickname()));

        if (method_exists($user, 'setEmail')) {
            $user->setEmail($userInformation->getEmail());
        }

        return $user;
    }
}
