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
}
