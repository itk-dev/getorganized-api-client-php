<?php

namespace ItkDev\GetOrganized\Mock;

use ItkDev\GetOrganized\Client as BaseClient;

final class Client extends BaseClient
{
    public function __construct()
    {
        parent::__construct('mock', 'mock', 'mock');
    }

    protected function setUpClient()
    {
        $this->client = new GuzzleClient();
    }
}
