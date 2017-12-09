<?php

namespace Paperyard\Controllers\Misc;

use Paperyard\Controllers\BasicController;
use Paperyard\Helpers\Enums\PluginType;
use Paperyard\Models\Document;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Index
 * @package Paperyard\Controllers\Misc
 */
class Index extends BasicController
{
    /**
     * Index constructor.
     * @param Twig $view
     * @param LoggerInterface $logger
     * @param Messages $flash
     */
    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;

        $this->registerPlugin('bootstrap-notify.min', PluginType::ONLY_JS);
        $this->registerPlugin('dropzone', PluginType::ONLY_JS);
        $this->registerPlugin('index', PluginType::ONLY_JS);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $this->view->render($response, 'index.twig', $this->render());
        return $response;
    }

    /**
     * render
     * @return array data to render the view
     */
    public function render()
    {
        return [
            'plugins' => parent::getPlugins(),
            'languageFlag' => parent::getLanguageFlag(),
            'scannedToday' => $this->documentsScanned(),
            'ocrFailures' => $this->ocrFailures(),
            'toConfirm' => $this->toConfirm(),
        ];
    }

    /**
     * documentsScanned
     *
     * Counts the total scanned documents.
     *
     * @return int total scanned documents
     */
    private function documentsScanned()
    {
        return \Paperyard\Models\Log\File::distinct()->get(['fileContent'])->count();
    }

    private function toConfirm()
    {
        $documents = Document::findAll(['/data/outbox/*.pdf', '/data/inbox/*.pdf']);

        $to_confirm = array_filter($documents, function($document) {
            return !$document['isConfirmed'];
        });

        return count($to_confirm);
    }

    private function ocrFailures()
    {
        return count(Document::findAll(['/data/scan/error/*.pdf']));
    }
}
