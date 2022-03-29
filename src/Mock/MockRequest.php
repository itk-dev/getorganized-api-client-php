<?php

namespace ItkDev\GetOrganized\Mock;

class MockRequest
{
    // @phpstan-ignore-next-line
    private string $method;

    // @phpstan-ignore-next-line
    private string $uri;

    // @phpstan-ignore-next-line
    private array $options;

    public function __construct(string $method, $uri = '', array $options = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
    }
}
