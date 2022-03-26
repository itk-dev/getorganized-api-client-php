<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Exception\GetOrganizedClientException;
use ItkDev\GetOrganized\Service;

class Cases extends Service
{
    protected function getApiBaseUrl(): string
    {
        return '/_goapi/Cases/';
    }

    /**
     * @throws GetOrganizedClientException
     */
    public function FindCases(array $body)
    {
        $result = $this->invoke(
            'POST',
            $this->getApiBaseUrl().__FUNCTION__,
            $body
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

        return [];
    }
}
