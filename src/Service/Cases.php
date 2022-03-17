<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Exception\GetOrganizedClientException;
use ItkDev\GetOrganized\Service;

class Cases extends Service
{
    protected function getApiBaseUrl(): string
    {
        return '/_goapi/Cases/';
    }

    /**
     * @throws GetOrganizedClientException
     */
    public function FindCases(array $body)
    {
        return $this->invoke(
            'POST',
            $this->getApiBaseUrl().__FUNCTION__,
            $body
        );
    }
}
