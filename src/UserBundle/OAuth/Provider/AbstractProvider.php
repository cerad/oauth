<?php

namespace Cerad\Bundle\UserBundle\OAuth\Provider;

use Buzz\Client\ClientInterface as HttpClientInterface;

use Buzz\Client\Curl as BuzzCurl;

use Buzz\Message\MessageInterface as HttpMessageInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Buzz\Message\Response as HttpResponse;

use GuzzleHttp\Client as GuzzleClient;

class AbstractProvider
{
    protected function httpRequest($url, $content = null, $headers = array(), $method = null)
    {
        if (null === $method) {
            $method = null === $content ? HttpRequestInterface::METHOD_GET : HttpRequestInterface::METHOD_POST;
        }

        $contentLength = 0;
        if (is_string($content)) {
            $contentLength = strlen($content);
        } elseif (is_array($content)) {
            $contentLength = strlen(implode('', $content));
        }

        $headers = array_merge(
            array(
                'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
                'Content-Length: ' . $contentLength,
            ),
            $headers
        );
        print_r($headers); echo '<br />';
        /*
        $guzzleClient = new GuzzleClient([
            'defaults' => [
                'config' => [
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                    ]
                ]
            ]
        ]);*/
       
        $guzzleClient = new GuzzleClient();
      //$guzzleClient->setDefaultConfig('defaults/verify', false);
        try{
            $guzzleResponse = $guzzleClient->post($url,array(
                'headers' => $headers,
                'verify' => false,
            ));
        }
        catch (\Exception $e)
        {
            die('Response X Exception ' . $e->getMessage());
        }
die('ok');

        $buzzRequest  = new HttpRequest($method, $url);
        $buzzResponse = new HttpResponse();
        
        $buzzRequest->setHeaders($headers);
      //$buzzRequest->setContent($content);
        
        $buzzClient = new BuzzCurl();
        $buzzClient->setVerifyPeer(false);
        $buzzClient->send($buzzRequest, $buzzResponse);

        return $buzzResponse;
    }
    protected function getResponseContent(HttpMessageInterface $rawResponse)
    {
        // First check that content in response exists, due too bug: https://bugs.php.net/bug.php?id=54484
        $content = $rawResponse->getContent();
        if (!$content) {
            return array();
        }

        $response = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            parse_str($content, $response);
        }

        return $response;
    }
    
}
