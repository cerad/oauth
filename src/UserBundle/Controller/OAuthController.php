<?php

namespace Cerad\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthController extends Controller
{
    // Twitter http://local.oauth.zayso.org/oauth/callback?
    //   oauth_token=2Nqr6WnHISAQJfPq56ZiYCdiIfmnuaEKQHgAJOoss&
    //   oauth_verifier=GhbdJR1Y9ID5ZR4RiOoea8ucZDXUtWEFkaEGnGCfSc
    public function callbackAction(Request $request)
    {
        $providerManager = $this->get('cerad_user__oauth__provider_manager');
        
        $provider = $providerManager->createFromRequest($request);

        $accessToken = $provider->getAccessToken($request);

        $userInfo = $provider->getUserInfo($accessToken);
       
        $html = <<<EOT
<table>
<tr><td>Provider  </td><td>{$userInfo['providername']}</td></tr>
<tr><td>Identifier</td><td>{$userInfo['identifier'  ]}</td></tr>
<tr><td>User Name </td><td>{$userInfo['nickname'    ]}</td></tr>
<tr><td>Real Name </td><td>{$userInfo['realname'    ]}</td></tr>
<tr><td>Email     </td><td>{$userInfo['email'       ]}</td></tr>
</table>
EOT;
        return new Response($html);
        
    }
    // /oauth/authorize/providerName
    public function authorizeAction(Request $request, $providerName)
    {
        $providerManager = $this->get('cerad_user__oauth__provider_manager');
        
        $provider = $providerManager->createFromName($providerName);
        
        $authorizationUrl = $provider->getAuthorizationUrl($request);
    
        return new RedirectResponse($authorizationUrl);
    }
}
