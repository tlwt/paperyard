<?php


namespace Paperyard;

use Howtomakeaturn\PDFInfo\PDFInfo;

/**
 * Class ArchiveDocument
 *
 * @package Paperyard
 */
class ArchiveDocument
{

    const INDEX_DATE = 1;
    const INDEX_COMPANY = 2;
    const INDEX_SUBJECT = 3;
    const INDEX_RECIPIENT = 4;
    const INDEX_PRICE = 5;
    const INDEX_TAGS = 6;

    const DOC_TYPE_PDF = "pdf";
    const DOC_TYPE_OTHER = "other";

    /** @var string Filename with Extension. */
    public $name;

    /** @var string Filesize in a human readable format. */
    public $size;

    /** @var string Documents subject parsed from filename. */
    public $subject;

    /** @var string Documents tags parsed from filename. */
    public $tags;

    /** @var string Documents price parsed from filename. */
    public $price;

    /** @var string Documents recipient parsed from filename. */
    public $recipient;

    /** @var string Documents company parsed from filename. */
    public $company;

    /** @var string Documents date parsed from filename. */
    public $date;

    /** @var int Documents page count. */
    public $pages;

    /** @var string base64 string of filepath starting without / */
    public $identifier;

    /** @var string Absolute or relative path to document. */
    private $_full_path;

    /** @var string document type from extension */
    private $_document_type;

    /** @var array raw attribute from regex capture */
    private $_raw_attributes = [];


    public function __construct($full_path)
    {
        // everything starts with the filename
        $this->_full_path = $full_path;

        // might be handy later to have this info
        $this->_document_type = (pathinfo($this->_full_path, PATHINFO_EXTENSION) == "pdf" ? self::DOC_TYPE_PDF : self::DOC_TYPE_OTHER);

        // fill object with data
        $this->name = basename($this->_full_path);
        $this->size = $this->_humanFilesize($full_path);
        $this->date = $this->_parseDate();
        $this->company = $this->_parseAttribute(self::INDEX_COMPANY);
        $this->subject = $this->_parseAttribute(self::INDEX_SUBJECT);
        $this->recipient = $this->_parseAttribute(self::INDEX_RECIPIENT);
        $this->price = $this->_parseAttribute(self::INDEX_PRICE);
        $this->tags = $this->_parseAttribute(self::INDEX_TAGS);
        $this->pages = $this->_getNumberOfPages($full_path);
        $this->identifier = base64_encode($full_path);
    }

    /**
     * parseDate
     *
     * Gets raw date attribute and converts it to d.m.Y
     *
     * @todo date format customizable
     * @return false|string date or false on failure
     */
    private function _parseDate() {
        return date_format(date_create($this->_parseAttribute(self::INDEX_DATE)), 'd.m.Y');
    }

    /**
     * parseAttribute
     *
     * Capture and cache attributes with regular expression. Return on demand.
     *
     * @param $attr int index of capture group
     * @return string attribute value
     */
    private function _parseAttribute($attr) {

        // fill if still empty
        if ($this->_raw_attributes == []) {
            preg_match('/(.*?) - (.*?) - (.*?) \((.*?)\) \((.*?)\) \[(.*?)\]/', $this->name, $this->_raw_attributes);
        }

        return $this->_raw_attributes[$attr];
    }

    /**
     * getNumberOfPages.
     *
     * @param $full_path string Absolute or relative path to pdf
     * @return int number of pages
     */
    private function _getNumberOfPages($full_path) {
        $pdf = new PDFInfo($full_path);
        return (int)$pdf->pages;
    }

    /**
     * humanFilesize
     *
     * Convert bytes to a human readable format.
     * Based on http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
     *
     * @param string $full_path Path to file
     * @param int $decimals decimal places
     * @return string human readable filesize
     */
    private function _humanFilesize($full_path, $decimals = 2)
    {
        $bytes = filesize($full_path);
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }

}