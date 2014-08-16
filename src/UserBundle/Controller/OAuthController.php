<?php

namespace Cerad\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Cerad\Bundle\UserBundle\OAuth\Provider\GithubProvider;

class OAuthController extends Controller
{
    const SESSION_KEY = 'cerad_user__oauth';
    
    protected function getCallbackUri(Request $request)
    {
        // http://local.oauth.zayso.org/oauth/callback
        $httpUtils = $this->container->get('security.http_utils');
        return $httpUtils->generateUri($request,'cerad_user__oauth_callback');
    }
    protected function getProvider($name)
    {
        $clientId     = $this->container->getParameter($name . '_client_id');
        $clientSecret = $this->container->getParameter($name . '_client_secret');
                
        $providerClass = 'Cerad\\Bundle\\UserBundle\\OAuth\\Provider\\' . ucfirst($name) . 'Provider';
        
        return new $providerClass($clientId,$clientSecret);
    }
    public function callbackAction(Request $request)
    {
        $providerData = $request->getSession()->get(self::SESSION_KEY);
        $providerName = $providerData['providerName'];
        
        $provider = $this->getProvider($providerName);
        
        $code  = $request->get('code');
        
      //$state = $request->get('state');

        $accessToken = $provider->getAccessToken($code,$this->getCallbackUri($request));

        $userProfile = $provider->getUserProfile($accessToken);
        
        $userName = $userProfile['login'];
        $name     = $userProfile['name'];
        $email    = $userProfile['email'];
        
        $html = <<<EOT
<table>
<tr><td>Provider</td><td>$providerName</td></tr>
<tr><td>Username</td><td>$userName</td></tr>
<tr><td>Name    </td><td>$name</td></tr>
<tr><td>Email   </td><td>$email</td></tr>
</table>
EOT;
        return new Response($html);
        
        print_r($userResponse->json());
        die('done');
      //$body = $response->getBody()->getContents();
        echo sprintf('Response %d<br />',$response->getStatusCode());
        print_r($response->json());
        die(get_class($response));
        $accessTokenUrl .= '?' . http_build_query($params);
        
        die('OAuth Callback ' . $accessTokenUrl);
    }
    // oauth/authorize/provider
    public function authorizeAction(Request $request, $providerName)
    {
        $provider = $this->getProvider($providerName);
        
        $request->getSession()->set(self::SESSION_KEY,array('providerName' => $providerName));
        
        $authorizationUrl = $provider->getAuthorizationUrl($this->getCallbackUri($request));
    
        return new RedirectResponse($authorizationUrl);
        
      //die('oauth authorize ' . $provider . ' ' . $callbackUri);
    }
}
