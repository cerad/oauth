<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use GuzzleHttp\Client;
/* 
 * Array ( 
 *   [login] => ahundiak 
 *   [id] => 130533 
 *   [avatar_url]  => https://avatars.githubusercontent.com/u/130533?v=2 
 *   [gravatar_id] => 071bc4c7c6229920fd24f2f37d42b382 
 *   [url] => https://api.github.com/users/ahundiak 
 *   [html_url] => https://github.com/ahundiak 
 *   [followers_url] => https://api.github.com/users/ahundiak/followers 
 *   [following_url] => https://api.github.com/users/ahundiak/following{/other_user} 
 *   [gists_url] => https://api.github.com/users/ahundiak/gists{/gist_id} 
 *   [starred_url] => https://api.github.com/users/ahundiak/starred{/owner}{/repo} 
 *   [subscriptions_url] => https://api.github.com/users/ahundiak/subscriptions 
 *   [organizations_url] => https://api.github.com/users/ahundiak/orgs 
 *   [repos_url] => https://api.github.com/users/ahundiak/repos 
 *   [events_url] => https://api.github.com/users/ahundiak/events{/privacy} 
 *   [received_events_url] => https://api.github.com/users/ahundiak/received_events 
 *   [type] => User 
 *   [site_admin] => 
 *     [name] => Artx Hundiak 
 *     [company] => 
 *     [blog] => 
 *     [location] => 
 *     [email] => ahundiak@gmail.com 
 *     [hireable] => [bio] => [public_repos] => 4 [public_gists] => 0 
 *     [followers] => 2 [following] => 0 [created_at] => 2009-09-23T20:30:26Z [updated_at] => 2014-08-16T10:24:25Z )
 */
class GithubProvider
{
    protected $name = 'github';
    
    protected $clientId;
    protected $clientSecret;
    
    protected $scope;
    protected $userProfileUrl;
    protected $accessTokenUrl;
    protected $authorizationUrl;
    
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