<?php

namespace ItkDev\GetOrganized;

use ItkDev\GetOrganized\Exception\InvalidServiceNameException;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface ClientInterface
{
    /**
     * @throws InvalidServiceNameException
     */
    public function api(string $name): Service;

    public function request(string $method, string $url, array $options = []): ResponseInterface;
}
