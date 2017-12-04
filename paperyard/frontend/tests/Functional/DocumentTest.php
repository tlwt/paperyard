<?php

namespace Tests;

class DocumentTestTest extends \PHPUnit_Framework_TestCase
{
    const STANDARD_FILENAME_NO_TAG = '20171123 - Sample File - Paperyard (John Doe) (EUR1000,00) [nt] -- sample.pdf';

    /**
     * Test that filename parsing detects empty tag
     */
    public function testDocumentFilenameParseDetectEmptyTag()
    {
        $document = new \Paperyard\Models\Document(realpath('./') . '/tests/Functional/' . self::STANDARD_FILENAME_NO_TAG);
        $this->assertEquals('nt', $document->tags);
    }

    /**
     * Test that filename parsing detects empty tag
     */
    public function testDocumentMassFillMutability()
    {
        $document = new \Paperyard\Models\Document(realpath('./') . '/tests/Functional/' . self::STANDARD_FILENAME_NO_TAG);
        $document->fill(['name' => 'shouldOverwrite']);
        $this->assertEquals(self::STANDARD_FILENAME_NO_TAG, $document->name);
    }
}