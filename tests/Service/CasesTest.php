<?php

declare(strict_types=1);

namespace ItkDev\GetOrganized\Service;

use PHPUnit\Framework\TestCase;
use ItkDev\GetOrganized\Mock\MockClient;

final class CasesTest extends TestCase
{
    /**
     * @dataProvider findCasesDataProvider
     */
    public function testFindCases(array $query, $expected): void
    {
        $client = new MockClient();
        /** @var Cases $service */
        $service = $client->api('cases');

        $actual = $service->FindCases($query);
        $this->assertEquals($expected, $actual);
    }

    public function findCasesDataProvider(): iterable
    {
        yield [
            [
                'CaseIdFilter' => 'AKT-*',
                'IncludeRegularCases' => true,
                'IncludeOrphanedCases' => false,
                'StartItemIndex' => 0,
                'ItemCount' => 30,
                'CustomProperties' => '',
            ],

            [
                // 'ResultsXml' => '<Cases><ServiceStatus Status="Success" /><Case CaseID="AKT-2021-000032" Name="test sag" /><Case CaseID="AKT-2021-000034" Name="Test sag1" /><Case CaseID="AKT-2021-000046" Name="Testsag" /><Case CaseID="AKT-2021-000050" Name="Test aktindsigt" /><Case CaseID="AKT-2021-000052" Name="Indkøbsprocessen 2023" /><Case CaseID="AKT-2021-000054" Name="testsag" /><Case CaseID="AKT-2021-000056" Name="sag" /><Case CaseID="AKT-2021-000058" Name="kha test" /><Case CaseID="AKT-2021-000060" Name="Akt sag" /><Case CaseID="AKT-2021-000062" Name="FDLTest TC014" /><Case CaseID="AKT-2021-000064" Name="FDLtest" /><Case CaseID="AKT-2021-000066" Name="testsas" /><Case CaseID="AKT-2021-000068" Name="test" /><Case CaseID="AKT-2021-000070" Name="Testtest" /><Case CaseID="AKT-2021-000072" Name="Aktindsigt i Adam Galai testdokumenter" /><Case CaseID="AKT-2021-000073" Name="Test af opret aktindsigt fra en emnesag" /><Case CaseID="AKT-2021-000074" Name="Test af opret aktindsigt fra en emnesag 2" /><Case CaseID="AKT-2021-000075" Name="testakt" /><Case CaseID="AKT-2021-000076" Name="Anmodning om aktindsigt fra journalist i referat på sag &quot;Adam Galai - testsag 2&quot;" /><Case CaseID="AKT-2021-000077" Name="Testakt2" /><Case CaseID="AKT-2021-000078" Name="test" /><Case CaseID="AKT-2021-000079" Name="abc" /><Case CaseID="AKT-2021-000080" Name="Test dfgui" /><Case CaseID="AKT-2021-000081" Name="navn" /><Case CaseID="AKT-2021-000082" Name="test for Damgaard" /><Case CaseID="AKT-2021-000083" Name="Aktindsigt - test1" /><Case CaseID="AKT-2021-000084" Name="Test aktindsigt TD004" /><Case CaseID="AKT-2021-000085" Name="Test sagsnavn" /><Case CaseID="AKT-2021-000086" Name="ffm - testsag" /><Case CaseID="AKT-2021-000087" Name="ffm - testsag" /></Cases>',
                ['CaseID' => 'AKT-2021-000032', 'Name' => 'test sag'],
                ['CaseID' => 'AKT-2021-000034', 'Name' => 'Test sag1'],
                ['CaseID' => 'AKT-2021-000046', 'Name' => 'Testsag'],
                ['CaseID' => 'AKT-2021-000050', 'Name' => 'Test aktindsigt'],
                ['CaseID' => 'AKT-2021-000052', 'Name' => 'Indkøbsprocessen 2023'],
                ['CaseID' => 'AKT-2021-000054', 'Name' => 'testsag'],
                ['CaseID' => 'AKT-2021-000056', 'Name' => 'sag'],
                ['CaseID' => 'AKT-2021-000058', 'Name' => 'kha test'],
                ['CaseID' => 'AKT-2021-000060', 'Name' => 'Akt sag'],
                ['CaseID' => 'AKT-2021-000062', 'Name' => 'FDLTest TC014'],
                ['CaseID' => 'AKT-2021-000064', 'Name' => 'FDLtest'],
                ['CaseID' => 'AKT-2021-000066', 'Name' => 'testsas'],
                ['CaseID' => 'AKT-2021-000068', 'Name' => 'test'],
                ['CaseID' => 'AKT-2021-000070', 'Name' => 'Testtest'],
                ['CaseID' => 'AKT-2021-000072', 'Name' => 'Aktindsigt i Adam Galai testdokumenter'],
                ['CaseID' => 'AKT-2021-000073', 'Name' => 'Test af opret aktindsigt fra en emnesag'],
                ['CaseID' => 'AKT-2021-000074', 'Name' => 'Test af opret aktindsigt fra en emnesag 2'],
                ['CaseID' => 'AKT-2021-000075', 'Name' => 'testakt'],
                ['CaseID' => 'AKT-2021-000076', 'Name' => 'Anmodning om aktindsigt fra journalist i referat på sag "Adam Galai - testsag 2"'],
                ['CaseID' => 'AKT-2021-000077', 'Name' => 'Testakt2'],
                ['CaseID' => 'AKT-2021-000078', 'Name' => 'test'],
                ['CaseID' => 'AKT-2021-000079', 'Name' => 'abc'],
                ['CaseID' => 'AKT-2021-000080', 'Name' => 'Test dfgui'],
                ['CaseID' => 'AKT-2021-000081', 'Name' => 'navn'],
                ['CaseID' => 'AKT-2021-000082', 'Name' => 'test for Damgaard'],
                ['CaseID' => 'AKT-2021-000083', 'Name' => 'Aktindsigt - test1'],
                ['CaseID' => 'AKT-2021-000084', 'Name' => 'Test aktindsigt TD004'],
                ['CaseID' => 'AKT-2021-000085', 'Name' => 'Test sagsnavn'],
                ['CaseID' => 'AKT-2021-000086', 'Name' => 'ffm - testsag'],
                ['CaseID' => 'AKT-2021-000087', 'Name' => 'ffm - testsag'],
            ],
        ];
    }
}
