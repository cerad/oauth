<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

class GoogleProvider extends AbstractProvider
{   
    protected $scope = 'openid profile email';
    
    protected $userInfoUrl      = 'https://www.googleapis.com/oauth2/v2/userinfo';
    protected $accessTokenUrl   = 'https://accounts.google.com/o/oauth2/token';
    protected $authorizationUrl = 'https://accounts.google.com/o/oauth2/auth';
    
    public function getUserInfo($accessToken)
    {
        $data = $this->getUserInfoData($accessToken);
        
        $nameParts = explode('@',$data['email']);
        $nickname = count($nameParts) ? $nameParts[0] : null;
        
        $userInfo = array(
            'identifier'     => $data['id'],
            'nickname'       => $nickname,
            'realname'       => $data['name'],
            'email'          => $data['email'],
            'profilepicture' => null,
            'providername'   => $this->name,
        );
        return $userInfo;
    }
    /* Array ( 
     *   [id] => 110360268001715642098 
     *   [email] => ahundiak@zayso.org 
     *   [verified_email] => 1 
     *   [name] => Arthur Hundiak 
     *   [given_name] => Arthur
     *   [family_name] => Hundiak
     *   [link] => https://plus.google.com/110360268001715642098
     *   [picture] => https://lh3.googleusercontent.com/-KDDHRXRz07U/AAAAAAAAAAI/AAAAAAAAAAo/_e8-j-zb2os/photo.jpg 
     *   [gender] => male [locale] => en [hd] => zayso.org 
     * )
     * 
     */
}