<?php

namespace ItkDev\GetOrganized;

use DOMDocument;
use ItkDev\GetOrganized\Exception\GetOrganizedClientException;

abstract class Service
{
    /**
     * Get API base url.
     */
    abstract protected function getApiBasePath(): string;

    protected ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Send request.
     *
     * @throws GetOrganizedClientException
     */
    protected function request(string $method, string $url, array $options = [])
    {
        return $this->client->request($method, $url, $options);
    }

    /**
     * Get data from API endpoint.
     *
     * @throws GetOrganizedClientException
     */
    protected function getData(string $method, string $url, array $options = [])
    {
        $response = $this->request($method, $url, $options);

        // The response body may be empty which will throw an exception in
        // Response::toArray(), but we don't want any errors in that case.
        try {
            return $response->toArray();
        } catch (\JsonException $jsonException) {
            return [];
        }
    }

    /**
     * Build XML metadata element from metadata name-value pairs.
     *
     * Metadata pairs will be set as attributes on a <z:row xmlns:z='#RowsetSchema'/> element, e.g.
     *
     *   ['ows_CustomProperty' => 'Another prop value', 'ows_CCMMustBeOnPostList' => 0]
     *
     * will be converted to
     *
     *   <z:row xmlns:z="#RowsetSchema" ows_CustomProperty="Another prop value" ows_CCMMustBeOnPostList="0"/>
     */
    protected function buildMetadata(array $metadata): string
    {
        $doc = new DOMDocument();
        $doc->loadXML('<z:row xmlns:z="#RowsetSchema"/>');
        /** @var \DOMElement $element */
        $element = $doc->documentElement;
        foreach ($metadata as $name => $value) {
            $element->setAttribute($name, $value);
        }

        return $doc->saveXML($element);
    }

    /**
     * Build metadata array from XML.
     *
     * Reverses transform in self::buildMetadata (which see).
     *
     * @throws \Exception
     */
    protected function parseMetadata(string $xml): array
    {
        $metadata = [];
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        /** @var \DOMElement $element */
        $element = $doc->documentElement;
        foreach ($element->attributes as $name => $attribute) {
            $metadata[$name] = $attribute->value;
        }

        return $metadata;
    }
}
