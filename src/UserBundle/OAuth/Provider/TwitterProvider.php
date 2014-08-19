<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;

/* ================================================================
 * Twitter basically does not support user login via oauth2 - very sad
 * Nor does it provide email even with oauth1
 * 
 * See how far we can get by stealing hwi code.
 */
class TwitterProvider extends AbstractProvider
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
    
    /* ==================================================================
     * Direct copy of HWI\Bundle\OAuthBundle\Security\OAuthUtils::signRequest
     */
    protected function httpRequest($url, $content = null, $parameters = array(), $headers = array(), $method = null)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $key . '="' . rawurlencode($value) . '"';
        }
/*
        if (!$this->options['realm']) {
            array_unshift($parameters, 'realm="' . rawurlencode($this->options['realm']) . '"');
        }
*/
        $headers[] = 'authorization: OAuth ' . implode(', ', $parameters);

        return parent::httpRequest($url, $content, $headers, $method);
    }

    protected function signRequest($method,$urlInput,$params,$clientSecret,$tokenSecret = '')
    {
        // Validate required parameters
        foreach (array('oauth_consumer_key','oauth_timestamp','oauth_nonce','oauth_signature_method','oauth_version') as $paramName) {
            if (!isset($params[$paramName])) {
                throw new \RuntimeException(sprintf('OAUTH1 SignRequest Parameter "%s" must be set.', $paramName));
            }
        }
        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }
        // Parse & add query params as base string parameters if they exists
        $queryParams = array();
        $urlParts = parse_url($urlInput);
        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $queryParams);
            $params += $queryParams;
        }
        // Remove default ports
        // Ref: Spec: 9.1.2
        $explicitPort = isset($urlParts['port']) ? $urlParts['port'] : null;
        if (('https' === $urlParts['scheme'] && 443 === $explicitPort) || 
            ( 'http' === $urlParts['scheme'] &&  80 === $explicitPort)) {
            $explicitPort = null;
        }
        // Remove query params from URL
        // Ref: Spec: 9.1.2
        $urlGenerated = sprintf('%s://%s%s%s', 
            $urlParts['scheme'], 
            $urlParts['host'  ], 
           ($explicitPort ? ':'.$explicitPort : ''), 
            isset($urlParts['path']) ? $urlParts['path'] : ''
        );
        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');
        // http_build_query should use RFC3986
        
        $parts = array(
            // HTTP method name must be uppercase
            // Ref: Spec: 9.1.3 (1)
            strtoupper($method),
            rawurlencode($urlGenerated),
            rawurlencode(str_replace(array('%7E', '+'), array('~', '%20'), http_build_query($params, '', '&'))),
        );
        $baseString = implode('&', $parts);
        
        // HMAC
        $keyParts = array(rawurlencode($clientSecret),rawurlencode($tokenSecret));
        
        $signature = hash_hmac('sha1', $baseString, implode('&', $keyParts), true);
        
        return base64_encode($signature);
    }
    protected function getRequestToken($callbackUri, $state)
    {
        $timestamp = time();

        // https://dev.twitter.com/docs/api/1/post/oauth/request_token
        // http://tools.ietf.org/html/rfc5849
        $params = array(
            'oauth_consumer_key'     => $this->clientId,
            'oauth_timestamp'        => $timestamp,
            'oauth_nonce'            => $state,
            'oauth_version'          => '1.0', // Optional
            'oauth_callback'         => $callbackUri,
            'oauth_signature_method' => 'HMAC-SHA1',
        );
        $sig = OAuthUtils::signRequest('POST',$this->requestTokenUrl,$params,$this->clientSecret,'','HMAC-SHA1');
        echo 'Sig 1 ' . $sig . '<br />';
        
        $params['oauth_signature'] = 
            $this->signRequest('POST',$this->requestTokenUrl,$params,$this->clientSecret);
        
        echo 'Sig 3 ' . $params['oauth_signature'] . '<br />';
        
        $apiResponse = $this->httpRequest($this->requestTokenUrl, null, $params, array(), 'POST');
        $responseInfo = $this->getResponseContent($apiResponse);
        print_r($responseInfo); die();
        // Make the Auth
        foreach($params as $key => $value) {
            $params[$key] = $key . '="' . rawurlencode($value) . '"';
        }
        
        // Headers
        $headers = array(
            'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
            'Content-Length: ' . 0,
            'Authorization: OAuth ' . implode(', ', $params),
        );
        print_r($headers); echo '<br />';
        $client = new Client();
        
        try{
            $response = $client->post($this->requestTokenUrl,array(
                'headers' => $headers,
                'body' => null,
            ));
        }
        catch (\Exception $e)
        {
            die('Response Exception ' . $e->getMessage());
        }
        $responseData = $response->json();
        
        if (is_array($responseData)) print_r($responseData);
        
        die('getRequestToken');

        $apiResponse = $this->httpRequest($url, null, $parameters, array(), HttpRequestInterface::METHOD_POST);
die('request token' . $url);

        $response = $this->getResponseContent($apiResponse);

        if (isset($response['oauth_problem'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['oauth_problem']));
        }

        if (isset($response['oauth_callback_confirmed']) && ($response['oauth_callback_confirmed'] != 'true')) {
            throw new AuthenticationException('Defined OAuth callback was not confirmed.');
        }

        if (!isset($response['oauth_token']) || !isset($response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        $response['timestamp'] = $timestamp;

        $this->storage->save($this, $response);

        return $response;        
    }
    public function getAuthorizationUrl($callbackUri,$state = 'SomeTwitterState')
    {
        $requestToken = $this->getRequestToken($callbackUri,$state);
        die('Twitter requestToken ' . $requestToken);
        
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