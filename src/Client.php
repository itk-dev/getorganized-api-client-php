<?php

namespace ItkDev\GetOrganized;

use ItkDev\GetOrganized\Exception\InvalidServiceNameException;
use ItkDev\GetOrganized\Service\Cases;
use ItkDev\GetOrganized\Service\Tiles;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client implements ClientInterface
{
    private string $username;
    private string $password;
    private string $baseUri;
    private HttpClientInterface $httpClient;

    public function __construct(string $username, string $password, string $baseUrl)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUri = $baseUrl;
    }

    /**
     * @throws InvalidServiceNameException
     */
    public function api(string $name): Service
    {
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

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->getHttpClient()->request($method, $url, $options);
    }

    protected function getHttpClient(): HttpClientInterface
    {
        if (null === $this->httpClient) {
            $this->httpClient = HttpClient::createForBaseUri($this->baseUri, [
                'auth_ntlm' => $this->username . ':' . $this->password,
            ]);
        }

        return $this->httpClient;
    }
}
