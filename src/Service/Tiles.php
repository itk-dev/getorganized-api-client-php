<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Service;

class Tiles extends Service
{
    protected function getApiBasePath(): string
    {
        return '/_goapi/Administration/';
    }

    /**
     * Gets the list of links and their attributes in the GerOrganized global tile base navigation.
     */
    public function GetTilesNavigation()
    {
        return $this->getData(
            'GET',
            $this->getApiBasePath().__FUNCTION__,
            []
        );
    }
}
