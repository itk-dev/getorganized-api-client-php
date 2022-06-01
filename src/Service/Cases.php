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
     * Example query:.
     *
     * $query = [
     *   'FieldProperties' => [
     *     [
     *       'InternalName' => 'ows_CaseID',
     *       'Value' => 'GEO-2022-033830'
     *     ],
     *   ],
     *   'CaseTypePrefixes' => ['GEO'],
     * ]
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
}
