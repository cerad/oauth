<?php

namespace Cerad\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class RequestTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cerad_user__oauth1__request_token')
            ->setDescription('Request Token')
        ;
    }
    protected function getProvider($name)
    {
        $container = $this->getContainer();
        $clientId     = $container->getParameter($name . '_client_id');
        $clientSecret = $container->getParameter($name . '_client_secret');
                
        $providerClass = 'Cerad\\Bundle\\UserBundle\\OAuth\\Provider\\' . ucfirst($name) . 'Provider';
        
        return new $providerClass($clientId,$clientSecret);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->getRequestToken();
        
        echo "Request Token\n";
        
        $provider = $this->getProvider('twitter');
        
        $callbackUrl = 'http://local.oauth.zayso.org/oauth/callback';
        
        $authorizationUrl = $provider->getAuthorizationUrl($callbackUrl);
        
    }
    protected function timeline()
    {
        $client = new Client(['base_url' => 'https://api.twitter.com/1.1/']);

        $oauth = new Oauth1([
            'consumer_key'    => '0xNr0FWrm6kVphaXzYRJiXI82',
            'consumer_secret' => 'nRo2QSMQa0je9Pqled41aeDqRyCZRKg068lhfCx03XaRVa6gbL',
            'token'           => '49477179-SI8dWVbjGEh1V7FXZoQCfWcwePHXCrSPvqFAso0RA',
            'token_secret'    => 'nRo2QSMQa0je9Pqled41aeDqRyCZRKg068lhfCx03XaRVa6gbL'
        ]);

        $client->getEmitter()->attach($oauth);

        // Set the "auth" request option to "oauth" to sign using oauth
        $res = $client->get('statuses/home_timeline.json', 
            [
                'auth'   => 'oauth',
                'debug'  => true,
                'verify' => false,
            ]);
    }
    protected function getRequestToken()
    {
        $client = new Client(['base_url' => 'https://api.twitter.com/oauth/']);

        $oauth = new Oauth1([
            'consumer_key'    => '3WZlpd6AvzGyySiZShPld0WIq',
            'consumer_secret' => 'tCxaVpTOTA5vcYTM59V5aJAfoEkd9XyxA2qaQ1E7zik6gKfMgl',
            'callback'        => 'http://local.oauth.zayso.org/oauth/callbackx',
          //'token'           => '49477179-SI8dWVbjGEh1V7FXZoQCfWcwePHXCrSPvqFAso0RA',
          //'token_secret'    => 'nRo2QSMQa0je9Pqled41aeDqRyCZRKg068lhfCx03XaRVa6gbL'
        ]);

        $client->getEmitter()->attach($oauth);

        // Set the "auth" request option to "oauth" to sign using oauth
        $response = $client->get('request_token', 
            [
                'auth'   => 'oauth',
                'debug'  => true,
                'verify' => false,
            ]);
        $responseData = array();
        parse_str($response->getBody(),$responseData);
        print_r($responseData);
    }
    /* Authorization: OAuth 
     * oauth_consumer_key="3WZlpd6AvzGyySiZShPld0WIq", 
     * oauth_nonce="51fab471a50880da93dd8edeca8250f884a592c8", 
     * oauth_signature="798PM62YgYUG8GTpEXXhl63SuHQ%3D", 
     * oauth_signature_method="HMAC-SHA1", 
     * oauth_timestamp="1408715639",
     * oauth_version="1.0"
     */
}
