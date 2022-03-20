<?php

namespace ItkDev\GetOrganized;

use GuzzleHttp\Client as GuzzleClient;
use ItkDev\GetOrganized\Exception\InvalidServiceNameException;
use ItkDev\GetOrganized\Service\Cases;
use ItkDev\GetOrganized\Service\Tiles;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface ClientInterface
{
    /**
     * @param string $name
     *
     * @return Service
     * @throws InvalidServiceNameException
     *
     */
    public function api(string $name): Service;

    public function request(string $method, string $url, array $options = []): ResponseInterface;
}
