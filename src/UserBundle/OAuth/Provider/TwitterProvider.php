<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;

// Note: Twitter still uses oauth1
class TwitterProvider
{
    protected $name = 'twitter';
    
    protected $clientId;
    protected $clientSecret;
    
    protected $scope;
    
    protected $userProfileUrl;
    protected $accessTokenUrl   = '';
    protected $revertTokenUrl;
    protected $authorizationUrl = 'https://api.twitter.com/oauth/authorize';
    
            'authorization_url' => 'https://api.twitter.com/oauth/authenticate',
            'request_token_url' => 'https://api.twitter.com/oauth/request_token',
            'access_token_url'  => 'https://api.twitter.com/oauth/access_token',
            'infos_url'         => 'https://api.twitter.com/1.1/account/verify_credentials.json',
     
    public function __construct(
        $clientId,
        $clientSecret,
        $scope            = null,
        $authorizationUrl = 'https://github.com/login/oauth/authorize',
        $accessTokenUrl   = 'https://github.com/login/oauth/access_token',
        $userProfileUrl   = 'https://api.github.com/user'
    )
    {
        $this->clientId         = $clientId;
        $this->clientSecret     = $clientSecret;
        $this->scope            = $scope;
        $this->userProfileUrl   = $userProfileUrl;
        $this->accessTokenUrl   = $accessTokenUrl;
        $this->authorizationUrl = $authorizationUrl;
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
                'Authorization'  => 'Bearer ' . $accessToken,
            ),
        ));
        // TODO: Add providerName
        return $response->json();
    }
}