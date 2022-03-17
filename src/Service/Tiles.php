<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Service;

class Tiles extends Service
{
    protected function getApiBaseUrl(): string
    {
        return '/_goapi/Administration/';
    }

    public function GetTilesNavigation()
    {
        return $this->invoke(
            'GET',
            $this->getApiBaseUrl().__FUNCTION__,
            []
        );
    }
}
