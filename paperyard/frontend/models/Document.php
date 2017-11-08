<?php

namespace Paperyard\Models;

use Howtomakeaturn\PDFInfo\PDFInfo;

class Document
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

    /** @var string sha256 hash of file contents */
    public $hash;


    /** @var string Absolute or relative path to document. */
    private $fullPath;

    /** @var string document type from extension */
    private $documentType;

    /** @var array raw attribute from regex capture */
    private $rawAttributes = [];

    /**
     * @param $full_path string
     */
    public function __construct($full_path)
    {
        // everything starts with the filename
        $this->fullPath = $full_path;

        // might be handy later to have this info
        $this->documentType = (pathinfo($this->fullPath, PATHINFO_EXTENSION) == "pdf" ? self::DOC_TYPE_PDF : self::DOC_TYPE_OTHER);

        // fill object with data
        $this->name = basename($this->fullPath);
        $this->size = $this->humanFilesize($full_path);
        $this->date = $this->parseDate();
        $this->company = $this->parseAttribute(self::INDEX_COMPANY);
        $this->subject = $this->parseAttribute(self::INDEX_SUBJECT);
        $this->recipient = $this->parseAttribute(self::INDEX_RECIPIENT);
        $this->price = $this->parseAttribute(self::INDEX_PRICE);
        $this->tags = $this->parseAttribute(self::INDEX_TAGS);
        $this->pages = $this->getNumberOfPages($full_path);
        $this->identifier = base64_encode($full_path);
        $this->hash = hash_file("sha256", $full_path);
    }

    /**
     * Returns all important document informations as an array.
     *
     * @return array
     */
    public function toArray() {
        return array(
            "name" => $this->name,
            "size" => $this->size,
            "date" => $this->date,
            "company" => $this->company,
            "subject" => $this->subject,
            "recipient" => $this->recipient,
            "price" => $this->price,
            "tags" => $this->tags,
            "pages" => $this->pages,
            "identifier" => $this->identifier,
            "hash" => $this->hash
        );
    }

    /**
     * Gets raw date attribute and converts it to d.m.Y.
     *
     * @todo date format customizable
     * @return false|string date or false on failure
     */
    private function parseDate() {
        return date_format(date_create($this->parseAttribute(self::INDEX_DATE)), 'd.m.Y');
    }

    /**
     * Capture and cache attributes with regular expression. Return on demand.
     *
     * @param $attr int index of capture group
     * @return string attribute value
     */
    private function parseAttribute($attr) {

        // fill if still empty
        if ($this->rawAttributes == []) {
            preg_match('/(.*?) - (.*?) - (.*?) \((.*?)\) \((.*?)\) \[(.*?)\]/', $this->name, $this->rawAttributes);
        }

        return $this->rawAttributes[$attr];
    }

    /**
     * Uses pdfinfo to get the number of pages.
     *
     * @param $full_path string Absolute or relative path to pdf
     * @return int number of pages
     */
    private function getNumberOfPages($full_path) {
        $pdf = new PDFInfo($full_path);
        return (int)$pdf->pages;
    }

    /**
     * Converts bytes to a human readable format.
     * Based on http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
     *
     * @param string $full_path Path to file
     * @param int $decimals decimal places
     * @return string human readable filesize
     */
    private function humanFilesize($full_path, $decimals = 2)
    {
        $bytes = filesize($full_path);
        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
    }
}