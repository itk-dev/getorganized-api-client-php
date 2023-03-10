<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Exception\GetOrganizedClientException;
use ItkDev\GetOrganized\Exception\InvalidResponseException;
use ItkDev\GetOrganized\Service;

class Cases extends Service
{
    protected function getApiBasePath(): string
    {
        return '/_goapi/Cases/';
    }

    /**
     * Gets cases based on a caseId pattern.
     *
     * Example query:
     *  $query = [
     *      'CaseIdFilter' => 'AKT-*',
     *      'IncludeRegularCases' => true,
     *      'IncludeOrphanedCases' => false,
     *      'StartItemIndex' => 0,
     *      'ItemCount' => 30,
     *      'CustomProperties' => '',
     *  ];
     *
     * @throws GetOrganizedClientException
     */
    public function FindCases(array $query)
    {
        $result = $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => $query],
        );

        if (isset($result['ResultsXml'])) {
            $xml = new \SimpleXMLElement($result['ResultsXml']);
            $cases = [];
            foreach ($xml->Case as $case) {
                $cases[] = [
                    'CaseID' => (string) $case['CaseID'],
                    'Name' => (string) $case['Name'],
                ];
            }

            return $cases;
        }

        throw new InvalidResponseException('ResultsXml missing in response');
    }

    /**
     * Finds cases by properties.
     *
     * Example query:
     * $query = [
     *   'FieldProperties' => [
     *     [
     *       'InternalName' => 'ows_CaseID',
     *       'Value' => 'GEO-2022-033830'
     *     ],
     *   ],
     *   'CaseTypePrefixes' => ['GEO'],
     * ];
     */
    public function FindByCaseProperties(array $query): array
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => $query],
        );
    }

    /**
     * Find case by id.
     */
    public function getByCaseId(string $caseId, string $caseTypePrefix = null): ?array
    {
        if (null === $caseTypePrefix) {
            // Use string before first hyphen as case type prefix.
            $caseTypePrefix = preg_replace('/-.+$/', '', $caseId);
        }

        $cases = $this->FindByCaseProperties([
            'FieldProperties' => [
                [
                    'InternalName' => 'ows_CaseID',
                    'Value' => $caseId,
                ],
            ],
            'CaseTypePrefixes' => [$caseTypePrefix],
        ]);

        return $cases['CasesInfo'][0] ?? null;
    }

    /**
     * Creates case.
     *
     * Example metadata:
     * $data = [
     *      'ows_Title' => '0123456789 - Test borger',
     *      'ows_CCMContactData' => 'Test borger;#;#0123456789;#;#',
     *      'ows_CCMContactData_CPR' => '0123456789',
     *      'ows_CaseStatus' => 'Åben',
     * ];
     *
     * Example metadata subcase:
     *
     * $data = [
     *      'ows_Title' => 'Undersag - test',
     *      'ows_CCMParentCase' => 'BOR-2022-000038',
     *      'ows_ContentTypeId' => '0x0100512AABDB08FA4fadB4A10948B5A56C7C01',
     *      'ows_CaseStatus' => 'Åben',
     * ];
     */
    public function createCase(string $caseTypePrefix, array $metadata, bool $returnWhenCaseFullyCreated = true): ?array
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath(),
            ['json' => [
                'CaseTypePrefix' => $caseTypePrefix,
                'MetadataXml' => $this->buildMetadata($metadata),
                'ReturnWhenCaseFullyCreated' => $returnWhenCaseFullyCreated,
            ]]
        );
    }

    /**
     * Gets case metadata.
     */
    public function Metadata(string $caseId): ?array
    {
        $result = $this->getData(
            'GET',
            $this->getApiBasePath().__FUNCTION__.'/'.$caseId,
        );

        if (isset($result['Metadata'])) {
            return $this->parseMetadata($result['Metadata']);
        }

        throw new InvalidResponseException('Metadata missing in response');
    }
}
