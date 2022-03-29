<?php

namespace ItkDev\GetOrganized\Mock;

class MockRequest
{
    private string $method;
    private string $uri;
    private array $options;

    public function __construct(string $method, $uri = '', array $options = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
    }
}
