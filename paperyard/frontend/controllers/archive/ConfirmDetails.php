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

class ConfirmDetails extends BasicController
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

        $this->registerPlugin('bootstrap-datepicker.min');
        $this->registerPlugin('confirm_details', PluginType::ONLY_JS);
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        // we encodes the path in case any special character is used
        $this->documentFullPath = base64_decode($request->getAttribute('path'));

        // test if document exists
        if (!file_exists($this->documentFullPath)) {
            $this->flash->addMessage('error', _('Document not found.'));
            return $response->withRedirect('/latest');
        }

        // display document details
        $this->view->render($response, 'archive/confirm_details.twig', $this->render());
        return $response;
    }

    public function render()
    {
        return array(
            'plugins' => parent::getPlugins(),
            'document' => new Document($this->documentFullPath)
        );
    }
}