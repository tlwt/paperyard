<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/subjects', function (Request $request, Response $response, array $args) {
    $rulesSubjectsView = new Paperyard\Views\RulesSubjectsView();
    return $this->view->render($response, 'rules_subjects.twig', $rulesSubjectsView->render());
});

$app->get('/rules/subjects/{id}', function (Request $request, Response $response, array $args) {
    $rulesSubjectsDetailsView = new Paperyard\Views\RulesSubjectsDetailsView($request->getAttribute('id'));
    return $this->view->render($response, 'rules_subjects_details.twig', $rulesSubjectsDetailsView->render());
});

$app->post('/rules/subjects/add', function (Request $request, Response $response, array $args) {
    $ruleSubjects = \Paperyard\RuleSubjects::fromPostValues($request->getParsedBody());
    $ruleSubjects->insert();
    return $response->withRedirect('/rules/subjects');
});

$app->post('/rules/subjects/{id}/delete', function (Request $request, Response $response, array $args) {
    $ruleSubjects =  \Paperyard\RuleSubjects::fromId($request->getAttribute('id'));
    $ruleSubjects->delete();
    return $response->withRedirect('/rules/subjects');
});

$app->post('/rules/subjects/{id}/save', function (Request $request, Response $response, array $args) {
    $ruleSubjects = \Paperyard\RuleSubjects::fromPostValues($request->getParsedBody());
    $ruleSubjects->update($request->getAttribute('id'));
    return $response->withRedirect('/rules/subjects');
});