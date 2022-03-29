<?php

namespace ItkDev\GetOrganized\Mock;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpClient\HttpClientTrait;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class RequestHelper
{
    use HttpClientTrait;

    private ?array $items = null;

    public function getResponse(string $method, $uri = '', array $options = []): ResponseInterface
    {
        $currentRequest = $this->buildRequest($method, $uri, $options);

        try {
            foreach ($this->getItems() as $item) {
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

        throw new \RuntimeException(self::jsonEncode([__METHOD__, func_get_args()]));
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
            $options['body'] = self::jsonEncode($options['json']);
            unset($options['json']);
        }

        return $options['body'] ?? null;
    }

    private function getItems(): array
    {
        if (null === $this->items) {
            $directory = __DIR__.'/resources';
            $files = (new Finder())->in($directory)->name('*.yaml');
            foreach ($files as $file) {
                $requestPath = preg_replace('/^'.preg_quote($directory.'/', '/').'|\.yaml$/', '', $file->getRealPath());
                $items = Yaml::parseFile($file->getRealPath());
                foreach ($items as $item) {
                    // Add some defaults.
                    if (!isset($item['request']['method'])) {
                        $item['request']['method'] = 'GET';
                    }
                    if (!isset($item['request']['path'])) {
                        $item['request']['path'] = $requestPath;
                    }
                    $this->items[] = $item;
                }
            }
        }

        return $this->items;
    }
}
