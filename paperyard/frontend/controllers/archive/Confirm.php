<?php

namespace Paperyard\Controllers\Archive;

use Paperyard\Controllers\BasicController;
use Paperyard\Helpers\Enums\PluginType;
use Paperyard\Models\Document;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

class Confirm extends BasicController
{
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
        $this->registerPlugin('confirm');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $this->view->render($response, 'archive/confirm.twig', $this->render());
        return $response;
    }

    public function render()
    {
        return array(
            'plugins' => parent::getPlugins(),
            'outboxFiles' => $this->getOutboxFile(),
            'inboxFiles' => $this->getInboxFiles()
        );
    }

    private function getOutboxFile()
    {
        return $this->getDocumentsFromPattern("/data/outbox/*.pdf");
    }

    private function getInboxFiles()
    {
        return $this->getDocumentsFromPattern("/data/inbox/*.pdf");
    }

    private function getDocumentsFromPattern($pattern)
    {
        // get files as strings from filesystem
        $pdfs = glob($pattern, GLOB_NOSORT);

        // get document information as array
        array_walk($pdfs, function (&$pdf) {
            $pdf = (new \Paperyard\Models\Document($pdf))->toArray();
        });

        return array_filter($pdfs, function ($pdf) {
            return !$pdf['isConfirmed'];
        });
    }
}