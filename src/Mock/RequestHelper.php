<?php

namespace ItkDev\GetOrganized\Mock;

use http\Client\Response;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class RequestHelper
{
    use HttpClientTrait;

    public function getResponse(string $method, $uri = '', array $options = []): ResponseInterface
    {
        $resourceFilename = __DIR__.'/resources/'.$uri.'.yaml';
        if (!file_exists($resourceFilename)) {
            throw new \RuntimeException(sprintf('Resource %s not found', $uri));
        }

        $currentRequest = $this->buildRequest($method, $uri, $options);

        try {
            $items = Yaml::parseFile($resourceFilename);
            foreach ($items as $item) {
                $request = $this->buildRequest(
                    $item['request']['method'] ?? $method,
                    $item['request']['uri'] ?? $uri,
                    $item['request']['options'] ?? []
                );
                if ($currentRequest == $request) {
                    $info = [
                        'http_code' => $item['response']['status'] ?? 200,
                        'response_headers' => $item['response']['headers'] ?? null,
                    ];
                    $body = $this->getBody($item['response']);

                    return new MockResponse($body, $info);
                }
            }
        } catch (ParseException $parseException) {
        }

        throw new \RuntimeException(json_encode([__METHOD__, func_get_args()]));
    }

    private function buildRequest(string $method, $uri, array $options): MockRequest
    {
        $options += ['base_uri' => 'https://example.com'];
        [$url, $options] = $this->prepareRequest($method, $uri, $options);

        return new MockRequest($method, $uri, $options);
    }

    private function getBody(array $options): ?string
    {
        if (isset($options['json'])) {
            $options['body'] = Utils::jsonEncode($options['json']);
            unset($options['json']);
        }

        return $options['body'] ?? null;
    }
}
