<?php

namespace ItkDev\GetOrganized\Mock;

use ItkDev\GetOrganized\Service as BaseService;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class Service extends BaseService
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    protected function getApiBaseUrl(): string
    {
        return '';
    }

    public function __call($name, $arguments)
    {
        $resourceFilename = $this->getResourceDirectory().$name.'.yaml';
        if (!file_exists($resourceFilename)) {
            throw new \RuntimeException(sprintf('Resource %s/%s not found', $this->name, $name));
        }

        $query = is_array($arguments) ? reset($arguments) : null;
        try {
            $requests = Yaml::parseFile($resourceFilename);
            foreach ($requests as $request) {
                if ($query == ($request['query'] ?? null)) {
                    $response = new Response($request);

                    return json_decode((string) $response->getBody(), true);
                }
            }
        } catch (ParseException $parseException) {
        }

        throw new \RuntimeException(sprintf('Invalid request: %s/%s with query %s', $this->name, $name, json_encode($query)));
    }

    private function getResourceDirectory(): string
    {
        $name = __DIR__.'/resources/'.$this->name.'/';

        if (!is_dir($name)) {
            throw new \RuntimeException(sprintf('Invalid service name: %s'));
        }

        return $name;
    }
}
