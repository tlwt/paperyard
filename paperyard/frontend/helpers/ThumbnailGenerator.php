<?php

namespace Paperyard\Helpers;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ThumbnailGenerator
{

    public function __construct(Twig $view, LoggerInterface $logger)
    {
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $this->view->render($response, 'home.twig');
        return $response;
    }

}