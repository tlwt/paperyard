<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/archive', function (Request $request, Response $response, array $args) {
    $rulesArchiveView = new Paperyard\Views\RulesArchiveView();
    return $this->view->render($response, 'rules_archive.twig', $rulesArchiveView->render());
});

$app->get('/rules/archive/{id}', function (Request $request, Response $response, array $args) {
    $rulesArchiveDetailsView = new Paperyard\Views\RulesArchiveDetailsView($request->getAttribute('id'));
    return $this->view->render($response, 'rules_archive_details.twig', $rulesArchiveDetailsView->render());
});

$app->post('/rules/archive/add', function (Request $request, Response $response, array $args) {
    $ruleArchive = \Paperyard\RuleArchive::fromPostValues($request->getParsedBody());
    $ruleArchive->insert();
    return $response->withRedirect('/rules/archive');
});

$app->post('/rules/archive/{id}/delete', function (Request $request, Response $response, array $args) {
    $ruleArchive =  \Paperyard\RuleArchive::fromId($request->getAttribute('id'));
    $ruleArchive->delete();
    return $response->withRedirect('/rules/archive');
});

$app->post('/rules/archive/{id}/save', function (Request $request, Response $response, array $args) {
    $ruleArchive = \Paperyard\RuleArchive::fromPostValues($request->getParsedBody());
    $ruleArchive->update($request->getAttribute('id'));
    return $response->withRedirect('/rules/archive');
});