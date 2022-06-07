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

        $bytes = $this->fileToIntArray($filePath);

        $result = $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => [
                'bytes' => $bytes,
                'CaseId' => $caseId,
                'ListName' => $listName,
                'FolderPath' => $folderPath,
                'FileName' => $fileName,
                'Metadata' => $this->buildMetadata($metadata),
                'Overwrite' => $overwrite,
            ]],
        );

        // Help the garbage collector.
        unset($bytes);

        return $result;
    }

    /**
     * Finalize (“journaliser”) a single document.
     */
    public function Finalize(int $documentId, array $parameters = [
        'ShouldCloseOpenTasks' => false,
    ])
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath().'Finalize/ByDocumentId',
            [
                'json' => [
                    'DocID' => $documentId,
                ] + $parameters,
            ],
        );
    }

    /**
     * Finalize (“journaliser”) a list of documents.
     */
    public function FinalizeMultiple(array $documentIds, array $parameters = [
        'ShouldCloseOpenTasks' => false,
    ])
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath().'FinalizeMultiple/ByDocumentId',
            [
                'json' => [
                    'DocumentIds' => $documentIds,
                ] + $parameters,
            ],
        );
    }

    /**
     * Un-finalize (“af-journaliser”) a list of documents.
     */
    public function UnmarkFinalized(array $documentIds, array $parameters = [
        'CheckInComment' => 'Unfinalize',
        'OnlyUnfinalize' => true,
    ])
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath().'UnmarkFinalizedByDocumentId',
            [
                'json' => [
                    'DocIds' => $documentIds,
                ] + $parameters,
            ],
        );
    }

    public function fileToIntArray(string $filename): array
    {
        $contents = file_get_contents($filename);
        $size = strlen($contents);
        // Use https://www.php.net/manual/en/class.splfixedarray.php to optimize memory usage.
        $ints = new \SplFixedArray($size);

        for ($i = 0; $i < $size; $i++) {
            $ints[$i] = ord($contents[$i]);
        }

        return $ints;
    }

    public function getDocumentsByCaseId(string $caseId): array
    {
        /** @var Cases $casesService */
        $casesService = $this->client->api('cases');
        $case = $casesService->getByCaseId($caseId);
        if (null === $case || !isset($case['RelativeUrl'])) {
            return [];
        }

        $relativeUrl = $case['RelativeUrl'];

        $url = sprintf('%1$s/_api/web/getFolderByServerRelativeUrl(\'%1$s/Dokumenter/\')/Files?$select=ListItemAllFields/*&$expand=ListItemAllFields', $relativeUrl);
        $data = $this->getData('GET', $url, [
            // Request JSON response.
            'headers' => ['accept' => 'application/json'],
        ]);

        return $data['value'] ?? [];
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
