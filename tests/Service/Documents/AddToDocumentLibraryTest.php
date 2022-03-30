<?php

declare(strict_types=1);

namespace ItkDev\GetOrganized\Service\Documents;

use ItkDev\GetOrganized\Mock\MockClient;
use ItkDev\GetOrganized\Service\Documents;
use PHPUnit\Framework\TestCase;

final class AddToDocumentLibraryTest extends TestCase
{
    /**
     * @dataProvider AddToDocumentLibraryProvider
     */
    public function testAddToDocumentLibrary(array $arguments, $expected): void
    {
        $client = new MockClient();
        /** @var Documents $service */
        $service = $client->api('documents');

        $actual = call_user_func_array([$service, 'AddToDocumentLibrary'], $arguments);
        $this->assertEquals($expected, $actual);
    }

    public function AddToDocumentLibraryProvider(): iterable
    {
        yield [
            [
                __DIR__.'/../../../src/Mock/resources/assets/pipfugl.png',
                'GEO-2022-000114',
            ],

            [
                'DocId' => 215820,
            ],
        ];

        yield [
            [
                __DIR__.'/../../../src/Mock/resources/assets/pipfugl.png',
                'GEO-2022-000114',
                'pipfugl.png',
                [
                    'ows_CustomProperty' => 'Another prop value',
                    'ows_CCMMustBeOnPostList' => 0,
                    ],
                true,
                'Dokumenter',
                '',
            ],

            [
                'DocId' => 215821,
            ],
        ];
    }
}
