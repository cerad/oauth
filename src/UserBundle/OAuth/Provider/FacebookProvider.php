<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;

class FacebookProvider
{
    protected $name = 'facebook';
    
    protected $clientId;
    protected $clientSecret;
    
    protected $scope;
    protected $userProfileUrl;
    protected $accessTokenUrl;
    protected $revokeTokenUrl;
    protected $authorizationUrl;
    
    public function __construct(
        $clientId,
        $clientSecret,
        $scope            = 'email',
        $authorizationUrl = 'https://www.facebook.com/dialog/oauth',
        $accessTokenUrl   = 'https://graph.facebook.com/oauth/access_token',
        $revokeTokenUrl   = 'https://graph.facebook.com/me/permissions',
        $userProfileUrl   = 'https://graph.facebook.com/me'
    )
    {
        $this->clientId         = $clientId;
        $this->clientSecret     = $clientSecret;
        $this->scope            = $scope;
        $this->userProfileUrl   = $userProfileUrl;
        $this->accessTokenUrl   = $accessTokenUrl;
        $this->ewvokeTokenUrl   = $revokeTokenUrl;
        $this->authorizationUrl = $authorizationUrl;
    }
    public function getName() { return $this->name; }
    
    public function getAuthorizationUrl($callbackUri,$state = 'SomeFacebookState')
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
        $responseBody = (string)$response->getBody();
        
        // No json here
        $responseData = array();
        parse_str($responseBody,$responseData);
        
      //print_r($responseData);
      //die('here');
      //$responseData = $response->json();
        
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
        print_r($response->json()); die();
        return $response->json();
    }
}