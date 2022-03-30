<?php

namespace ItkDev\GetOrganized\Mock;

use ItkDev\GetOrganized\Mock\Exception\InvalidRequestException;
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

        foreach ($this->getItems() as $item) {
            $request = $this->buildRequest(
                $item['request']['method'] ?? $method,
                $item['request']['uri'] ?? $uri,
                $item['request']['options'] ?? [],
                $item
            );
            if ($currentRequest->equals($request)) {
                $info = [
                    'http_code' => $item['response']['status'] ?? 200,
                    'response_headers' => $item['response']['headers'] ?? null,
                ];
                $body = $this->getBody($item['response']);

                return new MockResponse($body, $info);
            }
        }

        throw new InvalidRequestException($currentRequest);
    }

    /**
     * @return int[]|array
     */
    public function fileToIntArray(string $filename): array
    {
        $ints = [];
        $handle = fopen($filename, 'rb');
        while (!feof($handle)) {
            $bytes = fread($handle, 1024);
            if ($bytes) {
                $ints[] = array_map('ord', str_split($bytes));
            }
        }
        fclose($handle);

        return array_merge(...$ints);
    }

    private function buildRequest(string $method, $uri, array $options, array $item = []): MockRequest
    {
        $options += ['base_uri' => 'https://example.com'];

        if (isset($options['json']) && is_array($options['json'])) {
            if (isset($item['directory'])) {
                chdir($item['directory']);
            }
            array_walk($options['json'], function ($value) {
                if (preg_match('/^<@(?P<expression>.+)>$/', $value, $matches)) {
                    return $this->evaluate($matches['expression']);
                }

                return $value;
            });
        }

        [$url, $options] = $this->prepareRequest($method, $uri, $options);

        return new MockRequest($method, implode('', $url), $options);
    }

    private function evaluate(string $expression)
    {
        if (preg_match('/^(?P<function>[^(]+)\((?P<args>.+)\)$/', $expression, $matches)) {
            switch ($matches['function']) {
                case 'file_to_int_array':
                    $filename = preg_replace('/^(["\'])(?P<value>.*)\1$/', '$2', $matches['args']);

                    return $this->fileToIntArray($filename);
            }
        }

        throw new \RuntimeException(sprintf('Invalid expression: %s', $expression));
    }

    private function getBody(array $options): ?string
    {
        if (isset($options['json']) && is_array($options['json'])) {
            $data = $options['json'];
            $options['body'] = self::jsonEncode($data);
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
                $requestPath = '/'.preg_replace('/^'.preg_quote($directory.'/', '/').'|\.yaml$/', '', $file->getRealPath());
                try {
                    $items = Yaml::parseFile($file->getRealPath());
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            // Add some defaults.
                            if (!isset($item['request']['method'])) {
                                $item['request']['method'] = 'GET';
                            }
                            if (isset($item['request']['uri'])) {
                                // Resolve relative uri
                                if (0 !== strpos('/', $item['request']['uri'])) {
                                    $item['request']['uri'] = $requestPath.'/'.$item['request']['uri'];
                                }
                            } else {
                                $item['request']['uri'] = $requestPath;
                            }
                            $item['directory'] = $file->getPath();
                            $this->items[] = $item;
                        }
                    }
                } catch (ParseException $parseException) {
                    throw $parseException;
                    // Ignore parse exceptions.
                }
            }
        }

        return $this->items;
    }
}
