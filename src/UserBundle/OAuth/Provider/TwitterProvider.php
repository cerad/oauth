<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;

/* ================================================================
 * Twitter basically does not support user login via oauth2 - very sad
 * Nor does it provide email even with oauth1
 * 
 * See how far we can get by stealing hwi code.
 */
class TwitterProvider
{
    protected $name = 'twitter';
    
    protected $clientId;
    protected $clientSecret;
    
    protected $scope = null;
    
    protected $userProfileUrl   = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    protected $accessTokenUrl   = 'https://api.twitter.com/oauth/access_token';
    protected $requestTokenUrl  = 'https://api.twitter.com/oauth/request_token';
    protected $authorizationUrl = 'https://api.twitter.com/oauth/authorize';
    protected $authenticationUrl= 'https://api.twitter.com/oauth/authenticate';
     
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
                'Authorization'  => 'Bearer ' . $accessToken,
            ),
        ));
        // TODO: Add providerName
        return $response->json();
    }
}