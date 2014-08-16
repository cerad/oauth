<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;

class GoogleProvider
{
    protected $name = 'google';
    
    protected $clientId;
    protected $clientSecret;
    
    protected $scope;
    protected $userProfileUrl;
    protected $accessTokenUrl;
    protected $authorizationUrl;
    
    // https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email
    public function __construct(
        $clientId,
        $clientSecret,
        $scope            = 'openid profile email', // openid
        $authorizationUrl = 'https://accounts.google.com/o/oauth2/auth',   // base URI
        $accessTokenUrl   = 'https://accounts.google.com/o/oauth2/token',
        $userProfileUrl   = 'https://www.googleapis.com/oauth2/v2/userinfo' // not documented, v1 is depreciated
//      $userProfileUrl   = 'https://www.googleapis.com/plus/v1/people/me/openIdConnect'
//      $userProfileUrl   = 'https://www.googleapis.com/plus/v1/people/me' // people.get
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
    
    public function getAuthorizationUrl($callbackUri,$state = 'SomeGoogleState')
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
        $responseData = $response->json();
        print_r($responseData); die();
        // TODO: Add providerName
        return $response->json();
    }
}