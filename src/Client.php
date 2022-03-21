<?php

namespace ItkDev\GetOrganized;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use ItkDev\GetOrganized\Exception\GetOrganizedClientException;
use ItkDev\GetOrganized\Exception\InvalidServiceNameException;
use ItkDev\GetOrganized\Service\Cases;
use ItkDev\GetOrganized\Service\Tiles;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private string $username;
    private string $password;
    private string $baseUrl;
    protected ?GuzzleClient $client = null;

    public function __construct(string $username, string $password, string $baseUrl)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $name
     *
     * @return Service
     * @throws InvalidServiceNameException
     *
     */
    public function api(string $name): Service
    {
        if (null === $this->client) {
            $this->setUpClient();
        }

        switch ($name) {
            case 'tiles':
                $service = new Tiles($this);
                break;
            case 'cases':
                $service = new Cases($this);
                break;
            default:
                $message = sprintf('Undefined service "%s"', $name);
                throw new InvalidServiceNameException($message);
        }

        return $service;
    }

    /**
     * @throws GetOrganizedClientException
     */
    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        if (null === $this->client) {
            $this->setUpClient();
        }

        try {
            return $this->client->request($method, $uri, $options);
        } catch (GuzzleException $e) {
            throw new GetOrganizedClientException($e->getMessage(), $e->getCode(), $e);
        }
    }


    protected function setUpClient()
    {
        $this->client = new GuzzleClient([
            'base_uri' =>  $this->baseUrl,
            'auth' => [
                $this->username,
                $this->password,
                'ntlm',
                ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
