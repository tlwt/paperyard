<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args) {
    $indexView = new Paperyard\Views\IndexView();
    return $this->view->render($response, 'index.twig', $indexView->render());
});

$app->get('/docs', function (Request $request, Response $response, array $args) {
    $indexView = new Paperyard\Views\IndexView();
    return $this->view->render($response, 'index.twig', $indexView->render());
});


include "routes_senders.php";

include "routes_subjects.php";

include "routes_recipients.php";

include "routes_archive.php";