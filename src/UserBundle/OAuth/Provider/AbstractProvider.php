<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\UserBundle\OAuth\ProviderManager;

class AbstractProvider
{
    protected $providerManager;
    
    protected $name;
    
    protected $client;
    protected $clientId;
    protected $clientSecret;
    
    protected $scope;
    
    protected $userProfileUrl;
    protected $accessTokenUrl;
    protected $revokeTokenUrl;
    protected $authorizationUrl;
    
    public function __construct(ProviderManager $providerManager,$name,$clientId,$clientSecret)
    {
        $this->providerManager = $providerManager;
        
        $this->name         = $name;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        
        $this->client = new Client;
    }
    public function getName() { return $this->name; }
    
    public function getAuthorizationUrl(Request $request)
    {   
        $query = array(
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'scope'         => $this->scope,
            'redirect_uri'  => $this->getRedirectUri($request),
            'state'         => $this->generateState(),
        );
        $request = $this->client->createRequest('GET',$this->authorizationUrl,[
            'query' => $query,
        ]);
        return $request->getUrl();
    }
    public function getAccessToken(Request $request)
    {
        $query = array(
            'grant_type'    => 'authorization_code',
            'code'          => $request->query->get('code'),
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->getRedirectUri($request),
        );
        
        $response = $this->client->post($this->accessTokenUrl,array(
            'headers' => array('Accept' => 'application/json'),
            'body' => $query,
        ));
        $responseData = $this->getResponseData($response);

        return $responseData;
    }
    public function getUserInfoData($accessToken)
    {
        $response = $this->client->get($this->userInfoUrl,array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization'  => 'Bearer ' . $accessToken['access_token'],
            ),
        ));
        return $this->getResponseData($response);
    }    
    // Return array from either json or name-value
    protected function getResponseData($response)
    {
        $content = (string)$response->getBody();
        
        if (!$content) {
            return array();
        }
        $json = json_decode($content, true);
        if (JSON_ERROR_NONE === json_last_error()) {
            return $json;
        }
        $data = array();
        parse_str($content, $data);
        return $data;
    }
    protected function getRedirectUri(Request $request)
    {
        return $this->providerManager->getRedirectUri($request);
    }
    protected function generateState()
    {
        return $this->providerManager->generateState();
    }
}
