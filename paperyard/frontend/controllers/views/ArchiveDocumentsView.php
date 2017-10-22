<?php


namespace Paperyard\Views;

use Paperyard\BasicView;
use Paperyard\ArchiveDocument;

/**
 * Class ArchiveDocumentsView
 *
 * Loads infomation about an archive and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class ArchiveDocumentsView extends BasicView
{

    /** @var string current path to archive with respect to archive basepath */
    private $_archive_path;

    /** @var string base path to all archives */
    private $_root_path = "/data/outbox/";

    public function __construct($archive_path)
    {
        // call parent for db init
        parent::__construct();

        // set path
        $this->_archive_path = $archive_path;

        // breadcrumbs will always start this way
        $this->breadcrumbs = array(
            [_("Documents"), ""],
            [_("Archive"), "/docs/archive"]
        );

        // because we cant wrap tr in a
        $this->plugins = ["clickable-row"];

        // add current archive to breadcrumbs with links
        $archive_breadcrumbs = explode("/", $archive_path);
        $previous_breadcrumb = null;
        foreach($archive_breadcrumbs as $archive_breadcrumb){

            // preserve previous breadcrumb to build current url
            $this->breadcrumbs[] = [$archive_breadcrumb, "/docs/archive" . $previous_breadcrumb . "/" . $archive_breadcrumb];
            $previous_breadcrumb .= DIRECTORY_SEPARATOR . $archive_breadcrumb;
        }

    }

    /**
     * render
     *
     * @return array data to render the view
     */
    public function render()
    {
        return array(
            "parent" => parent::render(),
            "archives" => $this->_getArchives(),
            "files" => $this->_getFiles()
        );
    }

    /**
     * getArchives
     *
     * Finds all archives deeper than the current.
     *
     * @return array all deeper archives
     */
    private function _getArchives() {

        // combine to current path
        $archive_path = $this->_root_path . $this->_archive_path;

        // iterate over current folder and get every deeper folder
        $archives = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($archive_path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach($iterator as $file) {
            if($file->isDir()) {
                $archives[] = str_replace($this->_root_path, "", $file);
            }
        }
        return $archives;
    }

    /**
     * getFiles
     *
     * Find all pdf files in current archive (folder).
     *
     * @return ArchiveDocument[] PDFs
     */
    private function _getFiles() {

        // searchpattern for current archive
        $archive_search_pattern = $this->_root_path . $this->_archive_path . "/*.pdf";

        // get files as strings from filesystem
        $pdfs = glob($archive_search_pattern, GLOB_NOSORT);

        // convert string elements into ArchiveDocuments objects
        array_walk($pdfs, function(&$pdf) { $pdf = get_object_vars(new ArchiveDocument($pdf)); });

        return $pdfs;
    }
}