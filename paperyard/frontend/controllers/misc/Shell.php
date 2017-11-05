<?php

namespace Paperyard\Controllers\Misc;

use Paperyard\Controllers\BasicController;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

class Shell extends BasicController
{
    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;

        $this->registerPlugin('shell-log');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        if ($request->isXhr()) {
            // provide entities
            return $response->withJson(\Paperyard\Models\Log\Shell::where('id', '>=', (int)$request->getAttribute('since'))->orderBy('id', 'DESC')->take((int)$request->getAttribute('count'))->get());
        } else {
            // provide view
            $this->view->render($response, 'misc/shell.twig', $this->render());
        }
    }

    /**
     * render
     * @return array data to render the view
     */
    public function render()
    {
        return array(
            'plugins' => parent::getPlugins(),
            'languageFlag' => parent::getLanguageFlag()
        );
    }
}