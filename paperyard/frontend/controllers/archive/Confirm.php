<?php

namespace Paperyard\Controllers\Archive;

use Paperyard\Controllers\BasicController;
use Paperyard\Models\Document;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

class Confirm extends BasicController
{
    /** @var Document[] documents in inbox folder */
    private $inboxFiles;

    private $inboxScore;

    private $outboxFiles;

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

        $this->registerPlugin('ekko-lightbox.min');

        $this->inboxFiles = $this->getInboxFiles();
        $this->outboxFiles = $this->getOutboxFile();
        $this->inboxScore = $this->calculateInboxScore();
    }

    public function __invoke(Request $request, Response $response, $args)
    {

        $this->view->render($response, 'archive/confirm.twig', $this->render());
        return $response;
    }

    public function render() {
        return array(
            'plugins' => parent::getPlugins(),
            'inboxFiles' => $this->inboxFiles,
            'outboxFiles' => $this->outboxFiles,
            'inboxScrore' => $this->inboxScore
        );
    }

    private function getInboxFiles() {
        // searchpattern for current archive
        $archive_search_pattern = "/data/inbox/*.pdf";

        // get files as strings from filesystem
        $pdfs = glob($archive_search_pattern, GLOB_NOSORT);

        // no conversion to Documents objects as file tags are not fully filled
        array_walk($pdfs, function (&$pdf) {
            $pdf = (new Document($pdf))->toArray();
        });
        return $pdfs;
    }

    private function getOutboxFile() {
        // searchpattern for current archive
        $archive_search_pattern = "/data/outbox/*.pdf";

        // get files as strings from filesystem
        $pdfs = glob($archive_search_pattern, GLOB_NOSORT);

        // convert string elements into Documents objects
        array_walk($pdfs, function (&$pdf) {
            $pdf = (new Document($pdf))->toArray();
        });
        return $pdfs;
    }

    private function calculateInboxScore() {
        $compliant_sum = 0;
        foreach ($this->inboxFiles as $inboxFile) {
            $compliant_sum += (int)$inboxFile['compliantFields'];
        }
        $max_score = 4*count($this->inboxFiles);
        $average_score = $compliant_sum/$max_score;
        return floor($average_score*100);
    }
}