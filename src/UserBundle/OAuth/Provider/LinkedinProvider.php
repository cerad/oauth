<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;

/*
 * Array ( 
 * [firstName] => Art 
 * [headline] => -- 
 * [lastName] => Hundiak 
 * [siteStandardProfileRequest] => Array ( 
 *   [url] => http://www.linkedin.com/profile/view?id=94618208&authType=name&authToken=qYoo&trk=api*a3442353*s3514153* ) )
 * 
 * Array ( [emailAddress] => ahundiak@ayso894.org [formattedName] => Art Hundiak [id] => 2jSjTge1i1 )
 */
class LinkedinProvider extends AbstractProvider
{
    protected $userInfoUrl      = 'https://api.linkedin.com/v1/people/~:(id,formatted-name,email-address,picture-url)';
    protected $accessTokenUrl   = 'https://www.linkedin.com/uas/oauth2/accessToken';
    protected $authorizationUrl = 'https://www.linkedin.com/uas/oauth2/authorization';
    
    public function getUserInfo($accessToken)
    {
        $response = $this->client->get($this->userInfoUrl,array(
            'headers' => array(
                'Accept' => 'application/json',
              //'Authorization'  => 'bearer ' . $accessToken,
            ),
            'query' => array(
                'format' => 'json',
                'oauth2_access_token' => $accessToken['access_token'],
            ),
        ));
        $data = $this->getResponseData($response);
        
        $nameParts = explode('@',$data['emailAddress']);
        $nickname = count($nameParts) ? $nameParts[0] : null;
        
        $userInfo = array(
            'identifier'     => $data['id'],
            'nickname'       => $nickname,
            'realname'       => $data['formattedName'],
            'email'          => $data['emailAddress'],
            'profilepicture' => null,
            'providername'   => $this->name,
        );
        return $userInfo;        
    }
/*
 * Array ( 
 * [emailAddress] => ahundiak@ayso894.org 
 * [formattedName] => Art Hundiak 
 * [id] => 2jSjTge1i1 
 * )
 */

}