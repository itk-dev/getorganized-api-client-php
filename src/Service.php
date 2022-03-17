<?php

namespace ItkDev\GetOrganized;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ItkDev\GetOrganized\Exception\GetOrganizedClientException;

abstract class Service
{
    /**
     * Get API base url.
     */
    abstract protected function getApiBaseUrl(): string;

    private GuzzleClient $client;

    public function __construct(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Invoke API method.
     * @throws GetOrganizedClientException
     */
    protected function invoke(string $method, string $url, array $body)
    {
        try {
            $response = $this->client->request(
                $method,
                $url,
                [
                    'body' => json_encode($body),
                ]
            );
        } catch (GuzzleException $e) {
            throw new GetOrganizedClientException($e->getMessage(), $e->getCode(), $e);
        }

        return json_decode((string) $response->getBody(), true);
    }
}
