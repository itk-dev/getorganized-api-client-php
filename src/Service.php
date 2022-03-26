<?php

namespace ItkDev\GetOrganized;

use ItkDev\GetOrganized\Exception\GetOrganizedClientException;

abstract class Service
{
    /**
     * Get API base url.
     */
    abstract protected function getApiBasePath(): string;

    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Get data from API endpoint.
     *
     * @throws GetOrganizedClientException
     */
    protected function getData(string $method, string $url, array $options = [])
    {
        $response = $this->client->request($method, $url, $options);

        return $response->toArray();
    }
}
