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

$app->get('/rules/senders/{id}', function (Request $request, Response $response, array $args) {
    $rulesSendersDetailsView = new Paperyard\Views\RulesSendersDetailsView($request->getAttribute('id'));
    return $this->view->render($response, 'rules_senders_details.twig', $rulesSendersDetailsView->render());
});

$app->post('/rules/senders/add', function (Request $request, Response $response, array $args) {
    $ruleSender = \Paperyard\RuleSenders::fromPostValues($request->getParsedBody());
    $ruleSender->insert();
    return $response->withRedirect('/rules/senders');
});

$app->post('/rules/senders/{id}/delete', function (Request $request, Response $response, array $args) {
    $ruleSenders =  \Paperyard\RuleSenders::fromId($request->getAttribute('id'));
    $ruleSenders->delete();
    return $response->withRedirect('/rules/senders');
});

$app->post('/rules/senders/{id}/save', function (Request $request, Response $response, array $args) {
    $ruleSender = \Paperyard\RuleSenders::fromPostValues($request->getParsedBody());
    $ruleSender->update($request->getAttribute('id'));
    return $response->withRedirect('/rules/senders');
});