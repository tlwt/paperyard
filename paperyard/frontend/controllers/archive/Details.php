<?php

namespace Paperyard\Controllers\Archive;

use Paperyard\Controllers\BasicController;
use Paperyard\Models\Document;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

class Details extends BasicController
{
    /** @var string base path to all archives */
    private $rootPath = '/data/sort/';

    /** @var string absolute path to document */
    private $documentFullPath;

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
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response|static
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        // we encodes the path in case any special character is used
        $this->documentFullPath = base64_decode($request->getAttribute('path'));

        // test if document exists
        if (!file_exists($this->documentFullPath)) {
            $this->flash->addMessage('error', _('Document not found.'));
            return $response->withRedirect('/archive');
        }

        // display document details
        $this->view->render($response, 'archive/details.twig', $this->render());
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
            'document' => new Document($this->documentFullPath)
        );
    }
}