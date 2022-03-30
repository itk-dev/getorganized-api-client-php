<?php

declare(strict_types=1);

namespace ItkDev\GetOrganized\Service\Documents;

use ItkDev\GetOrganized\Mock\MockClient;
use PHPUnit\Framework\TestCase;

final class AddToDocumentLibraryTest extends TestCase
{
    /**
     * @dataProvider AddToDocumentLibraryProvider
     */
    public function testAddToDocumentLibrary(string $filename, array $options, $expected): void
    {
        $client = new MockClient();
        /** @var Documents $service */
        $service = $client->api('documents');

        $actual = $service->AddToDocumentLibrary($filename);
        $this->assertEquals($expected, $actual);
    }

    public function AddToDocumentLibraryProvider(): iterable
    {
        yield [
            __DIR__.'/../../../src/Mock/resources/assets/pipfugl.png',
            [],

            ['{"DocId": 215820}'],
        ];
    }
}
