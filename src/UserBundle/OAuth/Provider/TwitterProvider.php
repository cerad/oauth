<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

use Doctrine\Common\Util\Debug;

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
    
    protected $userProfileUrl   = 'https://api.twitter.com/1.1/account/verify_credentials.json?include_entities=false&skip_status=true';
    protected $accessTokenUrl   = 'https://api.twitter.com/oauth/access_token';
    protected $requestTokenUrl  = 'https://api.twitter.com/oauth/request_token';
    protected $authorizationUrl = 'https://api.twitter.com/oauth/authenticate';
     
    public function __construct($clientId,$clientSecret)
    {
        $this->clientId         = $clientId;
        $this->clientSecret     = $clientSecret;
    }
    public function getName() { return $this->name; }
    
    public function getAuthorizationUrl(SymfonyRequest $currentRequest,$callbackUri,$state = 'SomeTwitterState')
    {
        $requestTokenClient = new Client();

        $oauth = new Oauth1([
            'consumer_key'    => $this->clientId,
            'consumer_secret' => $this->clientSecret,
            'callback'        => $callbackUri,
        ]);

        $requestTokenClient->getEmitter()->attach($oauth);

        $requestTokenResponse = $requestTokenClient->post($this->requestTokenUrl,[
            'auth'   => 'oauth',
            'debug'  => false,
            'verify' => false,
        ]);
        $requestTokenResponseData = array();
        parse_str($requestTokenResponse->getBody(),$requestTokenResponseData);

        $sessionData = [
            'providerName'       => $this->name,
            'requestToken'       => $requestTokenResponseData['oauth_token'],
            'requestTokenSecret' => $requestTokenResponseData['oauth_token_secret'],
            'callbackConfirmed'  => $requestTokenResponseData['oauth_callback_confirmed'],
        ];
        $currentRequest->getSession()->set('cerad_user__oauth',$sessionData);
        
        $authorizationClient = new Client();
        $authorizationRequest = $authorizationClient->createRequest('GET',$this->authorizationUrl,[
            'query' => ['oauth_token' => $requestTokenResponseData['oauth_token']]
        ]);
        return $authorizationRequest->getUrl();
        
        $params = array(
            'oauth_token' => $requestTokenResponseData['oauth_token'],
        );
        print_r($sessionData); die();
        return $this->authorizationUrl . '?' . http_build_query($params);
    }
    public function getAccessTokenUrl()
    {
        return $this->accessTokenUrl;
    }
    public function getAccessTokenQuery($code)
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
    public function getAccessToken(SymfonyRequest $symfonyRequest,$callbackUri)
    {
        // Twitter might have a denied
        $storage = $symfonyRequest->getSession();
        $storageData = $storage->get('cerad_user__oauth');
        
        $accessTokenClient = new Client();
        
        $oauth = new Oauth1([
            'consumer_key'    => $this->clientId,
            'consumer_secret' => $this->clientSecret,
            'token'           => $storageData['requestToken'],
            'token_secret'    => $storageData['requestTokenSecret'],
            'verifier'        => $symfonyRequest->get('oauth_verifier'),
        ]);
        $accessTokenClient->getEmitter()->attach($oauth);

        $accessTokenResponse = $accessTokenClient->post($this->accessTokenUrl,[
            'auth'   => 'oauth',
            'debug'  => false,
            'verify' => false,
          //'body'   => ['oauth_verifier' => $symfonyRequest->get('oauth_verifier')]
        ]);
        $accessTokenResponseData = array();
        parse_str($accessTokenResponse->getBody(),$accessTokenResponseData);
        
        $storageData['accessToken']       = $accessTokenResponseData['oauth_token'];
        $storageData['accessTokenSecret'] = $accessTokenResponseData['oauth_token_secret'];
        
        $storageData['userId']     = $accessTokenResponseData['user_id'];
        $storageData['screenName'] = $accessTokenResponseData['screen_name'];
        
        $storage->set('cerad_user__oauth',$storageData);
        
        return $storageData;
        
        print_r($storageData); die();
        
    }
    public function getUserProfileUrl()
    {
        return $this->userProfileUrl;
    }
    public function getUserProfile(SymfonyRequest $symfonyRequest)
    {
        $storage = $symfonyRequest->getSession();
        $storageData = $storage->get('cerad_user__oauth');
        
        $userProfileClient = new Client();
        
        $oauth = new Oauth1([
            'consumer_key'    => $this->clientId,
            'consumer_secret' => $this->clientSecret,
            'token'           => $storageData['accessToken'],
            'token_secret'    => $storageData['accessTokenSecret'],
        ]);
        $userProfileClient->getEmitter()->attach($oauth);

        $userProfileResponse = $userProfileClient->get($this->userProfileUrl,[
            'auth'   => 'oauth',
            'debug'  => false,
            'verify' => false,
            'query'  => ['include_entities' => 'false', 'skip_status' => 'false'],
        ]);
        $userProfileResponseData = array();
        parse_str($userProfileResponse->getBody(),$userProfileResponseData);
        
        print_r($userProfileResponseData); die();
    }
    /*
     * Array ( 
     * [{"id":49477179,
     *   "id_str":"49477179",
     *   "name":"Art_Hundiak",
     *   "screen_name":"ahundiak",
     *   "location":"","description":"","url":null,
     *   "entities":{"description":{"urls":] => 
     *     Array ( [0] => ) [Canada)","geo_enabled":false,"verified":false,"statuses_count":0,"lang":"en",
     *    "contributors_enabled":false,"is_translator":false,"is_translation_enabled":false,
     *    "profile_background_color":"C0DEED","profile_background_image_url":"http:\/\/abs_twimg_com\/images\/themes\/theme1\/bg_png","profile_background_image_url_https":"https:\/\/abs_twimg_com\/images\/themes\/theme1\/bg_png","profile_background_tile":false,"profile_image_url":"http:\/\/abs_twimg_com\/sticky\/default_profile_images\/default_profile_3_normal_png","profile_image_url_https":"https:\/\/abs_twimg_com\/sticky\/default_profile_images\/default_profile_3_normal_png","profile_link_color":"0084B4","profile_sidebar_border_color":"C0DEED","profile_sidebar_fill_color":"DDEEF6","profile_text_color":"333333","profile_use_background_image":true,"default_profile":true,"default_profile_image":true,"following":false,"follow_request_sent":false,"notifications":false}] => )
     */
}