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
     * Example data:
     *  $data = [
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
    public function FindCases(array $data)
    {
        $result = $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => $data],
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
     * Example data:
     * $data = [
     *   'FieldProperties' => [
     *     [
     *       'InternalName' => 'ows_CaseID',
     *       'Value' => 'GEO-2022-033830'
     *     ],
     *   ],
     *   'CaseTypePrefixes' => ['GEO'],
     * ];
     */
    public function FindByCaseProperties(array $data): array
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => $data],
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
     * Example data:
     * $data = [
     *   'CaseTypePrefix' => 'BOR',
     *   'MetadataXml' =>
     *     "<z:row xmlns:z=\"#RowsetSchema\"
     *      ows_Title=\"0123456789 - Test borger\"
     *      ows_CCMContactData=\"Test borger;#;#0123456789;#;#\"
     *      ows_CCMContactData_CPR=\"0123456789\"
     *      ows_CaseStatus=\"Åben\"
     *      />",
     *  'ReturnWhenCaseFullyCreated' => true,
     * ];
     */
    public function CreateCase(array $data): ?array
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath(),
            ['json' => $data],
        );
    }

    /**
     * Creates SubCase.
     *
     * Example data:
     * $data = [
     *   'CaseTypePrefix' => 'BOR',
     *   'MetadataXml' =>
     *     "<z:row xmlns:z=\"#RowsetSchema\"
     *     ows_Title=\"Undersag - test\"
     *     ows_CCMParentCase=\"BOR-2022-000038\"
     *     ows_ContentTypeId=\"0x0100512AABDB08FA4fadB4A10948B5A56C7C01\"
     *     ows_CCMContactData_CPR=\"0123456789\"
     *     ows_CaseStatus=\"Åben\"/>",
     *  'ReturnWhenCaseFullyCreated' => true,
     *  ];
     */
    public function CreateSubCase(array $data): ?array
    {
        return $this->getData(
            'POST',
            $this->getApiBasePath(),
            ['json' => $data],
        );
    }
}
