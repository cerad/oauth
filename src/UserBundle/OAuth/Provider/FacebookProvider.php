<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

class FacebookProvider extends AbstractProvider
{
    protected $scope = 'email';
    
    protected $userInfoUrl      = 'https://graph.facebook.com/me';
    protected $accessTokenUrl   = 'https://graph.facebook.com/oauth/access_token';
    protected $revokeTokenUrl   = 'https://graph.facebook.com/me/permissions';
    protected $authorizationUrl = 'https://www.facebook.com/dialog/oauth';
    
    public function getUserInfo($accessToken)
    {
        $data = $this->getUserInfoData($accessToken);
        
        $userInfo = array(
            'identifier'     => $data['id'],
            'nickname'       => $data['username'],
            'realname'       => $data['name'],
            'email'          => $data['email'],
            'profilepicture' => null,
            'providername'   => $this->name,
        );
        return $userInfo;
    }
    /* Array ( 
     * [id] => 1530263836 
     * [email] => ahundiak@gmail.com 
     * [first_name] => Arthur 
     * [gender] => male 
     * [last_name] => Hundiak 
     * [link] => https://www.facebook.com/arthur.hundiak 
     * [location] => Array ( [id] => 112367628774835 [name] => Huntsville, Alabama ) [locale] => en_US 
     * [name] => Arthur Hundiak 
     * [timezone] => -5 [updated_time] => 2012-12-28T03:21:23+0000 
     * [username] => arthur.hundiak 
     * [verified] => 1 )
     * 
     */
}