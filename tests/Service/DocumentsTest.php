<?php

declare(strict_types=1);

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Mock\MockClient;
use PHPUnit\Framework\TestCase;

final class DocumentsTest extends TestCase
{
    /**
     * @dataProvider findCasesDataProvider
     */
    public function testGetMetadata(int $docId, $expected): void
    {
        $client = new MockClient();
        /** @var Documents $service */
        $service = $client->api('documents');

        $actual = $service->Metadata($docId);
        $this->assertEquals($expected, $actual);
    }

    public function findCasesDataProvider(): iterable
    {
        yield [
            215820,

            [
                'ows_Restricted' => '1;#',
                'ows_ContentVersion' => '1;#8',
                'ows_BSN' => '1;#149',
                'ows__ListSchemaVersion' => '1;#33',
                'ows__Dirty' => '1;#0',
                'ows__Parsable' => '1;#1',
                'ows__CommentFlags' => '1;#',
                'ows__CommentCount' => '1;#',
                'ows__LikeCount' => '1;#',
                'ows_Etag' => '{C2F1BD63-AAC9-4A89-B312-A025DCB3D47A},9',
                'ows_ParentUniqueId' => '1;#{35CB083B-296E-4CE6-9969-E01598AD1F5D}',
                'ows_StreamHash' => '1;#0x023035B1FEDF191A850BEA95635326054B859724C0',
                'ows_Title' => 'pipfugl',
                'ows_CCMCognitiveType' => '-1.00000000000000',
                'ows_CaseOwner' => '18;#IB_ERPO_MTM',
                'ows_Korrespondance' => 'Intern',
                'ows_Dato' => '2022-03-18 00:00:00',
                'ows_SvarPaa' => '',
                'ows_ErBesvaret' => '0',
                'ows_CaseID' => 'GEO-2022-000114',
                'ows_CCMVisualId' => 'GEO-2022-000114',
                'ows_DocID' => '215820',
                'ows_Finalized' => '0',
                'ows_Related' => '0',
                'ows_CaseRecordNumber' => '0',
                'ows_LocalAttachment' => '0',
                'ows_ExtendedDocIcon' => 'png',
                'ows_CCMLinkTitleFilename' => 'pipfugl',
                'ows_CCMTemplateID' => '0',
                'ows_CCMSystemID' => '841e253c-373c-4a80-ac8a-3a11fad6e546',
                'ows_WasEncrypted' => '0',
                'ows_WasSigned' => '0',
                'ows_MailHasAttachments' => '0',
                'ows_CCMPinDocument' => '215820',
                'ows_CCMIsSharedOnOneDrive' => '0',
                'ows_CCMPageCount' => '0',
                'ows_CCMCommentCount' => '0',
                'ows_CCMPreviewAnnotationsTasks' => '0',
                'ows_CCMMetadataExtractionStatus' => 'CCMPageCount:NotSupported;CCMCommentCount:NotSupported',
                'ows_CCMCognitiveDisplayField' => '215820',
                'ows_CCMPreview' => '215820',
                'ows_TaxCatchAll' => '',
                'ows_Modtagere' => '',
                'ows_Part' => '',
                'ows_CCMMustBeOnPostList' => '0',
                'ows_ServerRedirected' => '0',
                'ows_IsShared' => '1',
            ],
        ];
    }
}
