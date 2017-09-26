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

$app->get('/rules/senders', function (Request $request, Response $response, array $args) {
    $rulesSendersView = new Paperyard\Views\RulesSendersView();
    return $this->view->render($response, 'rules_senders.twig', $rulesSendersView->render());
});

$app->post('/rules/senders/add', function (Request $request, Response $response, array $args) {
    $parsedBody = $request->getParsedBody();
});