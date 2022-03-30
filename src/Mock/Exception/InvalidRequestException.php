<?php

namespace ItkDev\GetOrganized\Mock\Exception;

use ItkDev\GetOrganized\Mock\MockRequest;

class InvalidRequestException extends \RuntimeException
{
    private ?MockRequest $request;

    public function __construct(MockRequest $request)
    {
        parent::__construct((string) $request);
        $this->request = $request;
    }

    public function getRequest(): ?MockRequest
    {
        return $this->request;
    }
}
