<?php

namespace Paperyard\Views;

use Paperyard\ArchiveDocument;
use Paperyard\BasicView;

/**
 * Class ArchiveDocumentsDetailsView
 *
 * Load Information about a document and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class ArchiveDocumentsDetailsView extends BasicView
{

    /** @var ArchiveDocument object of current document */
    private $_document;

    public function __construct($full_path)
    {
        // call parent for db init
        parent::__construct();

        // breadcrumbs as array of array [Text, URL]
        $this->breadcrumbs = array(
            [_("Archive"), ""],
            [_("Document"), ""]
        );

        // load all document details from path
        $this->_document = new ArchiveDocument($full_path);
    }

    /**
     * render
     *
     * @return array data to render the view
     */
    public function render() {
        return array(
            "parent" => parent::render(),
            "document" => $this->_document->toArray()
        );
    }
}