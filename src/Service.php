<?php

namespace ItkDev\GetOrganized;

use ItkDev\GetOrganized\Exception\GetOrganizedClientException;

abstract class Service
{
    /**
     * Get API base url.
     */
    abstract protected function getApiBaseUrl(): string;

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Invoke API method.
     */
    protected function invoke(string $method, string $url, array $body)
    {
        $response = $this->client->request(
            $method,
            $url,
            [
                'body' => json_encode($body),
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }
}
