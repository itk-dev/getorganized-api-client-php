<?php

namespace ItkDev\GetOrganized\Service;

use DOMDocument;
use ItkDev\GetOrganized\Exception\GetOrganizedClientException;
use ItkDev\GetOrganized\Exception\InvalidFilePathException;
use ItkDev\GetOrganized\Exception\InvalidResponseException;
use ItkDev\GetOrganized\Service;

class Documents extends Service
{
    protected function getApiBasePath(): string
    {
        return '/_goapi/Documents/';
    }

    /**
     * Gets document metadata without system fields by DocID.
     *
     * @throws GetOrganizedClientException
     */
    public function Metadata(int $docId)
    {
        $result = $this->getData(
            'GET',
            $this->getApiBasePath().__FUNCTION__.'/'.$docId
        );

        if (isset($result['Metadata'])) {
            return $this->parseMetadata($result['Metadata']);
        }

        throw new InvalidResponseException('Metadata missing in response');
    }

    /**
     * Adds document to chosen case.
     *
     * Example metadata:
     *  $metadata = [
     *      'ows_CustomProperty' => 'Another prop value',
     *      'ows_CCMMustBeOnPostList' => 0,
     *  ];
     *
     * @throws GetOrganizedClientException
     * @throws InvalidFilePathException
     */
    public function AddToDocumentLibrary(string $filePath, string $caseId, string $fileName = '', array $metadata = [], bool $overwrite = true, string $listName = 'Dokumenter', string $folderPath = '')
    {
        if (!file_exists($filePath)) {
            throw new InvalidFilePathException(sprintf('File %s does not exist', $filePath));
        }

        return $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => [
                'bytes' => $this->fileToIntArray($filePath),
                'CaseId' => $caseId,
                'ListName' => $listName,
                'FolderPath' => $folderPath,
                'FileName' => $fileName,
                'Metadata' => $this->buildMetadata($metadata),
                'Overwrite' => $overwrite,
            ]],
        );
    }

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
    private function buildMetadata(array $metadata): string
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
    private function parseMetadata(string $xml): array
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
