<?php

namespace ItkDev\GetOrganized;

use ItkDev\GetOrganized\Exception\GetOrganizedClientException;

abstract class Service
{
    /**
     * Get API base url.
     */
    abstract protected function getApiBasePath(): string;

    protected ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Send request.
     *
     * @throws GetOrganizedClientException
     */
    protected function request(string $method, string $url, array $options = [])
    {
        return $this->client->request($method, $url, $options);
    }

    /**
     * Get data from API endpoint.
     *
     * @throws GetOrganizedClientException
     */
    protected function getData(string $method, string $url, array $options = [])
    {
        $response = $this->request($method, $url, $options);

        // The response body may be empty which will throw an exception in
        // Response::toArray(), but we don't want any errors in that case.
        try {
            return $response->toArray();
        } catch (\JsonException $jsonException) {
            return [];
        }
    }
}
