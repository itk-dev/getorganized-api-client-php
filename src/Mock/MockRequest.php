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

    /**
     * Decide if this request is the same request as another request.
     */
    public function equals(MockRequest $that): bool
    {
        return $this->method === $that->method
            && $this->uri === $that->uri
            // Note use of Equal operator (rather than Identical).
            && $this->options == $that->options;
    }

    public function __toString()
    {
        return sprintf('%s %s', $this->method, $this->uri);
    }
}
