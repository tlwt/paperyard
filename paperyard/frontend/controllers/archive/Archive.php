<?php

namespace Paperyard\Controllers\Archive;

use Paperyard\Controllers\BasicController;
use Paperyard\Models\Document;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Documents
 * @package Paperyard\Controllers\Archive
 */
class Archive extends BasicController
{
    /** @var string base path to all archives */
    private $rootPath = '/data/sort';

    /** @var string current path to archive with respect to archive basepath (without leading slash) */
    private $archiveRelPath;

    /** @var string full path to archive (without ending slash) */
    private $archiveFullPath;

    /**
     * @param Twig $view
     * @param LoggerInterface $logger
     * @param Messages $flash
     */
    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;

        $this->registerPlugin('clickable-row');
        $this->registerPlugin('searchable-table');
        $this->registerPlugin('datatables.min');
        $this->registerPlugin('bootstrap-notify.min');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $this->archiveRelPath = $request->getAttribute('path');
        $this->archiveFullPath = $this->rootPath . $this->archiveRelPath;

        if (!is_dir($this->archiveFullPath)) {
            $this->flash->addMessage('error', _('Archive not found.'));
            return $response->withRedirect('/archive');
        }

        $this->view->render($response, 'archive/archive.twig', $this->render());
        return $response;
    }

    /**
     * @return array data to render the view
     */
    public function render()
    {
        return array(
            'plugins' => parent::getPlugins(),
            'languageFlag' => parent::getLanguageFlag(),
            'archives' => $this->getArchives(),
            'newestFiles' => $this -> getNewestFiles(),
            'files' => $this->getFiles()
        );
    }

    /**
     * Finds all archives deeper than the current.
     *
     * @return array all deeper archives
     */
    private function getArchives()
    {
        // combine to current path
        $archive_path = $this->rootPath . $this->archiveRelPath;

        // iterate over current folder and get every deeper folder
        $archives = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($archive_path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $archives[] = str_replace($this->rootPath, "", $file);
            }
        }
        return $archives;
    }

    /**
     * Finds all archives deeper than the current.
     *
     * @return array all deeper archives
     */
    private function getFiles()
    {
        // searchpattern for current archive
        $archive_search_pattern = $this->archiveFullPath . "/*.pdf";

        // get files as strings from filesystem
        $pdfs = glob($archive_search_pattern, GLOB_NOSORT);

        // convert string elements into ArchiveDocuments objects
        array_walk($pdfs, function (&$pdf) {
            $pdf = (new Document($pdf))->toArray();
        });
        return $pdfs;
    }

    /**
    * Sort all archives by date.
    * 
    * @return array sorted archives
    */
     private function getNewestFiles()
    {
        // get archive list
        $pdfs = $this -> getFiles();

        // sort archives by date
        usort($pdfs, function($a, $b){
            return $a['date'] < $b['date'];
        });
        return $pdfs;
    }
}