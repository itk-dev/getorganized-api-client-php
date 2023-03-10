<?php

namespace ItkDev\GetOrganized\Service;

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

        $bytes = self::fileToIntArray($filePath);

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

    /**
     * Relates documents with specified relation.
     *
     * Call RelationTypes to get available types.
     */
    public function RelateDocuments(int $parentDocumentId, array $childrenDocumentIds, int $relationType)
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            [
                'json' => [
                        'ParentDocId' => $parentDocumentId,
                        'ChildDocIds' => $childrenDocumentIds,
                        'RelationTypeId' => $relationType,
                    ],
            ],
        );
    }

    /**
     * Deletes relation between parent and child document.
     */
    public function DeleteRelation(int $parentDocumentId, int $childDocumentId)
    {
        return $this->getData(
            'DELETE',
            $this->getApiBasePath().'Relation/'.$parentDocumentId.'/'.$childDocumentId,
            [],
        );
    }

    /**
     * Get available document relation types.
     */
    public function RelationTypes()
    {
        return $this->getData(
            'GET',
            $this->getApiBasePath().__FUNCTION__,
            [],
        );
    }

    /**
     * Get contents of a file as an array of integers.
     *
     * @return array|\SplFixedArray An array for PHP prior to 8.1 and otherwise a SplFixedArray
     */
    public static function fileToIntArray(string $filename)
    {
        $contents = file_get_contents($filename);
        $size = strlen($contents);
        // Use https://www.php.net/manual/en/class.splfixedarray.php to optimize memory usage.
        $ints = new \SplFixedArray($size);

        for ($i = 0; $i < $size; ++$i) {
            $ints[$i] = ord($contents[$i]);
        }

        // For PHP prior to 8.1, convert to array to sure that it's JSON
        // serialized as an array (cf.
        // https://php.watch/versions/8.1/SplFixedArray-JsonSerializable-json_encode).
        return PHP_VERSION_ID < 801000 ? $ints->toArray() : $ints;
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
     * Add document to case using SOAP.
     *
     * Using the JSON api requires converting the file to a list of bytes
     * (integers) which exhausts PHP resources for large files. SOAP only
     * requires base64 encoding the file which is much less resource intensive.
     *
     * @see AddToDocumentLibrary for details on parameters.
     */
    public function AddToCaseSOAP(string $filePath, string $caseId, string $fileName = '', array $metadata = [], bool $overwrite = true, string $listName = 'Dokumenter', string $folderPath = '')
    {
        if (!file_exists($filePath)) {
            throw new InvalidFilePathException(sprintf('File %s does not exist', $filePath));
        }

        // @see /_vti_bin/Document.asmx?op=AddToCase
        $sxe = new \SimpleXMLElement(<<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <AddToCase xmlns="http://netcompany.com/ncsolutions/ccm/webservices">
      <bytes/>
      <caseID/>
      <listName/>
      <folderPath/>
      <fileName/>
      <metadata/>
      <overwrite/>
    </AddToCase>
  </soap12:Body>
</soap12:Envelope>
XML
        );

        $sxe->registerXPathNamespace('soap12', 'http://www.w3.org/2003/05/soap-envelope');
        $sxe->registerXPathNamespace('netcompany', 'http://netcompany.com/ncsolutions/ccm/webservices');
        $addToCase = $sxe->xpath('//soap12:Body/netcompany:AddToCase')[0];

        $addToCase->bytes = base64_encode(file_get_contents($filePath));
        $addToCase->caseID = $caseId;
        $addToCase->listName = $listName;
        $addToCase->folderPath = $folderPath;
        $addToCase->fileName = $fileName;
        $addToCase->metadata = $this->buildMetadata($metadata);
        $addToCase->overwrite = $overwrite ? 'true' : 'false';

        // We need as much time as possible.
        set_time_limit(0);

        try {
            $result = $this->request(
                'POST',
                '/_vti_bin/Document.asmx',
                [
                    'body' => $sxe->asXML(),
                    'headers' => [
                        'content-type' => 'application/soap+xml; charset=utf-8',
                    ],
                    // We need as much time as possible.
                    'timeout' => 60 * 60,
                    'max_duration' => \INF,
                ]
            );

            $sxe = new \SimpleXMLElement((string) $result->getContent());
            $sxe->registerXPathNamespace('netcompany', 'http://netcompany.com/ncsolutions/ccm/webservices');
            $addToCaseResponse = $sxe->xpath('//netcompany:AddToCaseResponse')[0] ?? null;

            // Create results similar to the one returned by the JSON API.
            return [
                'DocId' => $addToCaseResponse ? (int) $addToCaseResponse->documentId : null,
            ];
        } catch (\Exception $exception) {
            return [];
        }
    }
}
