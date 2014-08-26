<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

class GithubProvider extends AbstractProvider
{
    protected $userInfoUrl      = 'https://api.github.com/user';
    protected $accessTokenUrl   = 'https://github.com/login/oauth/access_token';
    protected $authorizationUrl = 'https://github.com/login/oauth/authorize';
    
    public function getUserInfo($accessToken)
    {
        $data = $this->getUserInfoData($accessToken);
        
        $userInfo = array(
            'identifier'     => $data['id'],
            'nickname'       => $data['login'],
            'realname'       => $data['name'],
            'email'          => $data['email'],
            'profilepicture' => null,
            'providername'   => $this->name,
        );
        return $userInfo;
    }
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
}