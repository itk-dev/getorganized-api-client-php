<?php

namespace ItkDev\GetOrganized;

use GuzzleHttp\Client as GuzzleClient;
use ItkDev\GetOrganized\Exception\InvalidServiceNameException;
use ItkDev\GetOrganized\Service\Cases;
use ItkDev\GetOrganized\Service\Tiles;

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
     * @throws InvalidServiceNameException
     */
    public function api(string $name): Service
    {
        if (null === $this->client) {
            $this->setUpClient();
        }

        switch ($name) {
            case 'tiles':
                $service = new Tiles($this->client);
                break;
            case 'cases':
                $service = new Cases($this->client);
                break;
            default:
                $message = sprintf('Undefined service "%s"', $name);
                throw new InvalidServiceNameException($message);
        }

        return $service;
    }

    protected function setUpClient()
    {
        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
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
