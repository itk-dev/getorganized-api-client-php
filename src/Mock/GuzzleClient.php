<?php

namespace ItkDev\GetOrganized\Mock;

use GuzzleHttp\Client as BaseGuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class GuzzleClient extends BaseGuzzleClient
{
    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        $resourceFilename = __DIR__.'/resources/'.$uri.'.yaml';
        if (!file_exists($resourceFilename)) {
            throw new \RuntimeException(sprintf('Resource %s not found', $uri));
        }

        $query = json_decode($options['body'] ?? 'null', true);

        try {
            $requests = Yaml::parseFile($resourceFilename);
            foreach ($requests as $request) {
                if ($query == ($request['query'] ?? null)) {
                    return new Response($request);
                }
            }
        } catch (ParseException $parseException) {
        }


        throw new \RuntimeException(json_encode([__METHOD__, func_get_args()]));
    }
}
