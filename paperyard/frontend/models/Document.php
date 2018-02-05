<?php

namespace Paperyard\Models;

use Howtomakeaturn\PDFInfo\PDFInfo;
use Paperyard\Helpers\Enums\DocumentType;
use Valitron\Validator;

class Document
{
    /** @const REGEX_TAG matches comma separated tags composed of alphanum, special char and whitespace */
    const REGEX_TAG = '/^([ÄäÜüÖöß\sa-zA-Z0-9]+,)*[ÄäÜüÖöß\sa-zA-Z0-9]+$/';

    /** @const REGEX_STRING matches alphanum, special char and whitespace */
    const REGEX_STRING = '/^[ÄäÜüÖöß\sa-zA-Z0-9]*$/';

    /** @const REGEX_DATE matches sqlite style dates (Ymd) */
    const REGEX_DATE = '/^(0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).(20\d{2})$/';

    /** @const REGEX_PRICE matches special price format */
    const REGEX_PRICE = '/^\d+(\.\d{3})*(,\d{2})?$/';


    /** @const INDEX_DATE date capture group index */
    const INDEX_DATE = 1;

    /** @const INDEX_COMPANY date capture group index */
    const INDEX_COMPANY = 2;

    /** @const INDEX_SUBJECT date capture group index */
    const INDEX_SUBJECT = 3;

    /** @const INDEX_RECIPIENT date capture group index */
    const INDEX_RECIPIENT = 4;

    /** @const INDEX_PRICE date capture group index */
    const INDEX_PRICE = 5;

    /** @const INDEX_TAGS date capture group index */
    const INDEX_TAGS = 6;

    /** @const INDEX_OLD_FILENAME date capture group index */
    const INDEX_OLD_FILENAME = 7;


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

    /** @var string Documents old filename parsed from filename. */
    public $oldFilename;

    /** @var int Documents page count. */
    public $pages;

    /** @var string base64 string of filepath starting without / */
    public $identifier;

    /** @var string sha256 hash of file contents */
    public $hash;

    public $url;

    /** @var bool */
    public $isConfirmed;

    /** @var string Absolute or relative path to document. */
    private $fullPath;

    /** @var string document type from extension */
    private $documentType;

    /** @var array raw attribute from regex capture */
    private $rawAttributes = [];

    /** @var array holds errors from validation */
    private $errors = [];

    /** @var array defining mutable request keys and maps to object properties */
    private $fillable = [
        'document-subject' => 'subject',
        'document-tags' => 'tags',
        'document-price' => 'price',
        'document-recipient' => 'recipient',
        'document-company' => 'company',
        'document-date' => 'date'
    ];

    /** @var array validation rules for fields */
    private $rules = [
        'optional' => [
            ['document-subject'],
            ['document-tags'],
            ['document-price'],
            ['document-recipient'],
            ['document-company'],
            ['document-date']
        ],
        'regex' => [
            ['document-subject', self::REGEX_STRING],
            ['document-recipient', self::REGEX_STRING],
            ['document-company', self::REGEX_STRING],
            ['document-tags', self::REGEX_TAG],
            ['document-price', self::REGEX_PRICE],
            ['document-date', self::REGEX_DATE]
        ]
    ];

    /** @var array maps internal field names to readable labels */
    private $labels = [
        'document-subject' => 'Subject',
        'document-tags' => 'Tags',
        'document-price' => 'Price',
        'document-recipient' => 'Recipient',
        'document-company' => 'Company',
        'document-date' => 'Date'
    ];

    /**
     * @param $full_path string
     */
    public function __construct($full_path)
    {
        // everything starts with the filename
        $this->fullPath = $full_path;

        // might be handy later to have this info
        $this->documentType = (pathinfo($this->fullPath, PATHINFO_EXTENSION) == "pdf" ? DocumentType::PDF : DocumentType::OTHER);

        // fill object with data
        $this->name = basename($this->fullPath);
        $this->size = $this->humanFilesize($full_path);
        $this->hash = hash_file("sha256", $full_path);
        $this->pages = $this->getNumberOfPages($full_path);
        $this->identifier = base64_encode($full_path);
        $this->url = $this->getUrl($full_path);

        $this->parseDataFromFilename();

        $this->isConfirmed = $this->isConfirmed();
    }

    private function getUrl($full_path) {
        $path = explode('/', $full_path);
        unset($path[1]);
        array_walk($path, function(&$part) {
            $part = rawurlencode($part);
        });
        return implode('/', $path);
    }

    private function parseDataFromFilename()
    {
        $this->date = $this->parseAttribute(self::INDEX_DATE);
        $this->company = $this->parseAttribute(self::INDEX_COMPANY);
        $this->subject = $this->parseAttribute(self::INDEX_SUBJECT);
        $this->recipient = $this->parseAttribute(self::INDEX_RECIPIENT);
        $this->price = $this->parseAttribute(self::INDEX_PRICE);
        $this->tags = $this->parseAttribute(self::INDEX_TAGS);
        $this->oldFilename = $this->parseAttribute(self::INDEX_OLD_FILENAME);
    }

    /**
     * Returns all important document information as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
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
    private function parseAttribute($attr)
    {
        // fill if still empty
        if ($this->rawAttributes == []) {
            preg_match('/^(.*?) - (.*?) - (.*?) \((.*?)\) \(EUR(.*?)\) \[(.*?)\] -- (.*?)(?:.pdf)$/', $this->name, $this->rawAttributes);
        }

        if (!array_key_exists($attr, $this->rawAttributes)) {
            return "";
        }

        return $this->rawAttributes[$attr];
    }

    /**
     * Uses pdfinfo to get the number of pages.
     *
     * @param $full_path string Absolute or relative path to pdf
     * @return int number of pages
     */
    private function getNumberOfPages($full_path)
    {
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

    /**
     * Mass assignment.
     *
     * @param array $attributes
     * @return array|bool
     */
    public function fill(array $attributes)
    {
        // remove "", null, 0, false and "0"
        $attributes = array_filter($attributes);

        $this->validate($attributes);

        if (!empty($this->errors)) {
            return $this->errors;
        }

        // go through every mutable property
        foreach ($this->fillable as $post_attribute => $obj_property) {
            // check if property exists and has value in post attributes array
            if (property_exists($this, $obj_property) && array_key_exists($post_attribute, $attributes)) {
                if ($this->hasMutator($obj_property)) {
                    $mutator = $this->mutatorFor($obj_property);
                    $this->{$obj_property} = $this->{$mutator}($attributes[$post_attribute]);
                } else {
                    $this->{$obj_property} = $attributes[$post_attribute];
                }
            }
        }

        return true;
    }

    /**
     * Checks if mutator a method exists.
     *
     * @param $attribute
     * @return bool
     */
    private function hasMutator($attribute)
    {
        return method_exists($this, $this->mutatorFor($attribute));
    }

    /**
     * Creates mutator string for a given attribute name.
     * Attribute will be converted to first letter uppercase.
     *
     * @param $attribute
     * @return string
     */
    private function mutatorFor($attribute)
    {
        return 'set' . ucfirst($attribute) . 'Attribute';
    }

    /**
     * Mass assignment mutator.
     * Converts date post (m.d.Y) to Ymd.
     *
     * @param $date
     * @return false|string
     */
    private function setDateAttribute($date)
    {
        return \DateTime::createFromFormat("d.m.Y", $date)->format('Ymd');
    }

    /**
     * Mass assignment mutator.
     * Removes delimiter dot and adds double zero decimals if needed.
     *
     * @param $price
     * @return string
     */
    private function setPriceAttribute($price)
    {
        $dotless = str_replace(".", "", $price);

        if (strpos($dotless, ",") === false) {
            return $dotless . ",00";
        }

        return $dotless;
    }
    
    /**
     * Mass assignment mutator.
     * Checks for empty tags.
     *
     * @param $tags
     * @return string
     */
    private function setTagsAttribute($tags)
    {
        if (empty($tags)) {
            return 'nt';
        }

        return $tags;
    }

    public function save()
    {
        $format = '%d - %s - %s (%s) (EUR%s) [%s] -- %s.pdf';
        $filename = sprintf(
            $format,
            $this->date,
            $this->company,
            $this->subject,
            $this->recipient,
            $this->price,
            $this->tags,
            $this->oldFilename);

        $dir = dirname($this->fullPath);
        $new_fullpath = $dir . DIRECTORY_SEPARATOR . $filename;
        rename($this->fullPath, $new_fullpath);
        $this->fullPath = $new_fullpath;
    }

    public function confirm()
    {
        // tag string to array
        $tags = explode(',', $this->tags);

        // trim
        $trimmed = array_map('trim', $tags);

        // cleaning - remove nt and ok (if present)
        $cleaned = array_diff($trimmed, ['nt', 'ok']);

        // confirm - adding ok
        $cleaned[] = 'ok';

        // glue
        $this->tags = implode(',', $cleaned);
    }

    private function isConfirmed()
    {
        // tag string to array
        $tags = explode(',', $this->tags);

        return in_array('ok', $tags);
    }

    /**
     * @param array $attributes
     */
    private function validate(array $attributes)
    {
        // new validator object
        $validator = new Validator($attributes);

        // pass rules
        $validator->rules($this->rules);

        // add labels (don't show internal names)
        $validator->labels($this->labels);

        // check rules
        if(!$validator->validate()) {
            $this->errors = $validator->errors();
        }
    }

    /**
     * Returns document objects for all pdfs found in the paths provided.
     *
     * @param array $paths Path to search in
     * @return Document[]
     */
    public static function findAll(array $paths)
    {
        array_walk($paths, function (&$path) {
            $path = glob($path);
        });

        $pdfs = Document::flatten($paths);

        array_walk($pdfs, function (&$pdf) {
            $pdf = (new \Paperyard\Models\Document($pdf))->toArray();
        });

        return $pdfs;
    }

    private static function flatten(array $array) {
        $return = array();
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }
}