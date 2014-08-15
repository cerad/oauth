<?php

namespace Cerad\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use GuzzleHttp\Client;

class OAuthController extends Controller
{
    public function callbackAction(Request $request)
    {
        $code  = $request->get('code');
        $state = $request->get('state');
        
        $httpUtils = $this->container->get('security.http_utils');
        $callbackUri = $httpUtils->generateUri($request,'cerad_user__oauth_callback');

        $accessTokenParams = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $this->container->getParameter('github_client_id'),
            'client_secret' => $this->container->getParameter('github_client_secret'),
            'redirect_uri'  => $callbackUri,
        );
        $accessTokenUrl = 'https://github.com/login/oauth/access_token';
      //$accessTokenUrl .= '?' . http_build_query($params);
        
        $client = new Client();
      //$response = $client->
        $accessTokenResponse = $client->post($accessTokenUrl,array(
            'headers' => array('Accept' => 'application/json'),
            'body' => $accessTokenParams
        ));
        $accessTokenData = $accessTokenResponse->json();
        
        $accessToken = $accessTokenData['access_token'];
        
        $userUrl = 'https://api.github.com/user';
        
        $userResponse = $client->get($userUrl,array(
            'headers' => array(
                'Accept' => 'application/json',
                'Authorization'  => 'token ' . $accessToken,
            ),
          //'query' => array('access_token' => $accessToken)
        ));
        $userResponseData = $userResponse->json();
        
        $provider = 'github';
        $userName = $userResponseData['login'];
        $name  = $userResponseData['name'];
        $email = $userResponseData['email'];
        
        $html = <<<EOT
<table>
<tr><td>Provider</td><td>$provider</td></tr>
<tr><td>User    </td><td>$userName</td></tr>
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
    public function authorizeAction(Request $request, $provider)
    {
        // http://local.oauth.zayso.org/oauth/authorize/github
        $httpUtils = $this->container->get('security.http_utils');
        $callbackUri = $httpUtils->generateUri($request,'cerad_user__oauth_callback');
        
        $clientId = $this->container->getParameter('github_client_id');
        
        $authorizationUrl = 'https://github.com/login/oauth/authorize';
        
        $scope = null;
        $state = 'Random';
        
        $params = array(
            'response_type' => 'code',
            'client_id'     => $clientId,
            'scope'         => $scope,
            'redirect_uri'  => $callbackUri,
            'state'         => $state,
        );
        $authorizationUrl .= '?' . http_build_query($params);
        
        return new RedirectResponse($authorizationUrl);

        die($authorizeUrl);
        
        die('oauth authorize ' . $provider . ' ' . $callbackUri);
    }
}
