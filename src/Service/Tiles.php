<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Service;

class Tiles extends Service
{
    protected function getApiBasePath(): string
    {
        return '/_goapi/Administration/';
    }

    public function GetTilesNavigation()
    {
        return $this->getData(
            'GET',
            $this->getApiBasePath().__FUNCTION__,
            []
        );
    }
}
