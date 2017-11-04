<?php

namespace Paperyard\Controllers;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class IndexView
 * @package Paperyard\Controllers
 */
class IndexView
{
    private $view;
    private $logger;
    protected $table;

    public function __construct(Twig $view, LoggerInterface $logger)
    {
        $this->view = $view;
        $this->logger = $logger;
    }

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
            "scannedToday" => $this->documentsScanned(),
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
}
