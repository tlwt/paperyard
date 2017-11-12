<?php

namespace Paperyard\Controllers\Rule;

use Paperyard\Controllers\BasicController;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Archives
 * @package Paperyard\Controllers\Rule
 */
class Archives extends BasicController
{
    /**
     * Archives constructor.
     * @param Twig $view
     * @param LoggerInterface $logger
     * @param Messages $flash
     */
    public function __construct(Twig $view, LoggerInterface $logger, Messages $flash)
    {
        $this->view = $view;
        $this->logger = $logger;
        $this->flash = $flash;

        $this->registerPlugin('datatables.min');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {

        // show rule list
        $this->view->render($response, 'rule/archives.twig', $this->render());
        return $response;
    }

    /**
     * render
     * @return array data to render the view
     */
    public function render()
    {
        return array(
            'plugins' => parent::getPlugins(),
            'rules' => \Paperyard\Models\Rule\Archive::all(),
            'languageFlag' => parent::getLanguageFlag()
        );
    }
}
