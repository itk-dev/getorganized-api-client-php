<?php

namespace ItkDev\GetOrganized\Mock;

use ItkDev\GetOrganized\Client;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MockClient extends Client
{
    private RequestHelper $requestHelper;

    public function __construct()
    {
        parent::__construct('mock', 'mock', 'mock');
        $this->requestHelper = new RequestHelper();
    }

    protected function getHttpClient(): HttpClientInterface
    {
        $callback = function ($method, $url, $options) {
            // Remove scheme and domain from url.
            $path = preg_replace('@^[a-z]+://[^/]+@', '', $url);

            return $this->requestHelper->getResponse($method, $path, $options);
        };

        return new MockHttpClient($callback);
    }
}
