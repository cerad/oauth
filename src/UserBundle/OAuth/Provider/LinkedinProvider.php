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
class LinkedinProvider
{
    protected $name = 'linkedin';
    
    protected $clientId;
    protected $clientSecret;
    
    protected $scope = null;
    protected $userProfileUrl   = 'https://api.linkedin.com/v1/people/~:(id,formatted-name,email-address,picture-url)';
    protected $accessTokenUrl   = 'https://www.linkedin.com/uas/oauth2/accessToken';
    protected $authorizationUrl = 'https://www.linkedin.com/uas/oauth2/authorization';
    
    public function __construct($clientId,$clientSecret)
    {
        $this->clientId         = $clientId;
        $this->clientSecret     = $clientSecret;
    }
    public function getName() { return $this->name; }
    
    public function getAuthorizationUrl($callbackUri,$state = 'SomeGithubState')
    {
        $params = array(
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'scope'         => $this->scope,
            'redirect_uri'  => $callbackUri,
            'state'         => $state,
        );
        return $this->authorizationUrl . '?' . http_build_query($params);
    }
    public function getAccessTokenUrl()
    {
        return $this->accessTokenUrl;
    }
    public function getAccessTokenQuery($code,$callbackUri)
    {
        $accessTokenQuery = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $callbackUri,
        );
        return $accessTokenQuery;
    }
    public function getAccessToken($code,$callbackUri)
    {
        $client = new Client();
        
        $response = $client->post($this->accessTokenUrl,array(
            'headers' => array('Accept' => 'application/json'),
            'body' => $this->getAccessTokenQuery($code,$callbackUri)
        ));
        $responseData = $response->json();
        
        return $responseData['access_token'];
    }
    public function getUserProfileUrl()
    {
        return $this->userProfileUrl;
    }
    public function getUserProfile($accessToken)
    {
        $client = new Client();
        
        $response = $client->get($this->userProfileUrl,array(
            'headers' => array(
                'Accept' => 'application/json',
              //'Authorization'  => 'bearer ' . $accessToken,
            ),
            'query' => array(
                'format' => 'json',
                'oauth2_access_token' => $accessToken
            ),
        ));
        // TODO: Add providerName
        return $response->json();
    }
}