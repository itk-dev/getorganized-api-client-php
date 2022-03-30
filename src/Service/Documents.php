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
     * @throws GetOrganizedClientException
     */
    public function Metadata(int $docId)
    {
        $result = $this->getData(
            'GET',
            $this->getApiBasePath().__FUNCTION__.'/'.$docId
        );

        if (isset($result['Metadata'])) {
            // {
            //   "Metadata": "<z:row xmlns:z=\"#RowsetSchema\" ows_Restricted=\"1;#\" ows_ContentVersion=\"1;#8\" ows_BSN=\"1;#149\" ows__ListSchemaVersion=\"1;#33\" ows__Dirty=\"1;#0\" ows__Parsable=\"1;#1\" ows__CommentFlags=\"1;#\" ows__CommentCount=\"1;#\" ows__LikeCount=\"1;#\" ows_Etag=\"{C2F1BD63-AAC9-4A89-B312-A025DCB3D47A},9\" ows_ParentUniqueId=\"1;#{35CB083B-296E-4CE6-9969-E01598AD1F5D}\" ows_StreamHash=\"1;#0x023035B1FEDF191A850BEA95635326054B859724C0\" ows_Title=\"pipfugl\" ows_CCMCognitiveType=\"-1.00000000000000\" ows_CaseOwner=\"18;#IB_ERPO_MTM\" ows_Korrespondance=\"Intern\" ows_Dato=\"2022-03-18 00:00:00\" ows_SvarPaa=\"\" ows_ErBesvaret=\"0\" ows_CaseID=\"GEO-2022-000114\" ows_CCMVisualId=\"GEO-2022-000114\" ows_DocID=\"215820\" ows_Finalized=\"0\" ows_Related=\"0\" ows_CaseRecordNumber=\"0\" ows_LocalAttachment=\"0\" ows_ExtendedDocIcon=\"png\" ows_CCMLinkTitleFilename=\"pipfugl\" ows_CCMTemplateID=\"0\" ows_CCMSystemID=\"841e253c-373c-4a80-ac8a-3a11fad6e546\" ows_WasEncrypted=\"0\" ows_WasSigned=\"0\" ows_MailHasAttachments=\"0\" ows_CCMPinDocument=\"215820\" ows_CCMIsSharedOnOneDrive=\"0\" ows_CCMPageCount=\"0\" ows_CCMCommentCount=\"0\" ows_CCMPreviewAnnotationsTasks=\"0\" ows_CCMMetadataExtractionStatus=\"CCMPageCount:NotSupported;CCMCommentCount:NotSupported\" ows_CCMCognitiveDisplayField=\"215820\" ows_CCMPreview=\"215820\" ows_TaxCatchAll=\"\" ows_Modtagere=\"\" ows_Part=\"\" ows_CCMMustBeOnPostList=\"0\" ows_ServerRedirected=\"0\" ows_IsShared=\"1\" />"
            // }
            $xml = new \SimpleXMLElement($result['Metadata']);
            $metadata = [];
            foreach ($xml->attributes() as $name => $value) {
                $metadata[$name] = (string) $value;
            }

            return $metadata;
        }

        throw new InvalidResponseException('Metadata missing in response');
    }

    /**
     * @throws GetOrganizedClientException
     * @throws InvalidFilePathException
     */
    public function AddToDocumentLibrary(string $filePath, string $caseId, string $fileName = '', array $metaDataOptions = [], bool $overWrite = true, string $listName = 'Dokumenter', string $folderPath = '')
    {
        if (!file_exists($filePath)) {
            throw new InvalidFilePathException(sprintf('File: %s does not exist', $filePath));
        }

        // File does exist, transform it into byte array via stream
        $fileByteArray = [];
        $stream = fopen($filePath, 'r');

        while (!feof($stream)) {
            $fileByteArray = array_merge($fileByteArray, $this->string2intArr(fgets($stream)));
        }

        fclose($stream);

        // Setup meta data
        $metaData = $this->computeMetaData($metaDataOptions);

        return $this->getData(
            'POST',
            $this->getApiBasePath().__FUNCTION__,
            ['json' => [
                'bytes' => $fileByteArray,
                'CaseId' => $caseId,
                'ListName' => $listName,
                'FolderPath' => $folderPath,
                'FileName' => $fileName,
                'Metadata' => $metaData->asXML(),
                'Overwrite' => $overWrite,
            ]],
        );
    }

    public function string2intArr($string)
    {
        $l = strlen($string);
        $r = [];
        for ($i = 0; $i < $l; ++$i) {
            $r[] = ord($string[$i]);
        }

        return $r;
    }

    private function computeMetaData(array $metaDataOptions)
    {
        // "<z:row xmlns:z='#RowsetSchema' ows_CustomProperty='Another prop value' />"
        $metadata = new \SimpleXMLElement('<z:row xmlns:z="#RowsetSchema"/>');
        foreach ($metaDataOptions as $name => $value) {
            $metadata->addAttribute($name, $value);
        }

        return $metadata;
    }
}
