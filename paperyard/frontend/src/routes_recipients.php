<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/recipients', function (Request $request, Response $response, array $args) {
    $rulesRecipientsView = new Paperyard\Views\RulesRecipientsView();
    return $this->view->render($response, 'rules_recipients.twig', $rulesRecipientsView->render());
});

$app->get('/rules/recipients/{id}', function (Request $request, Response $response, array $args) {
    $rulesRecipientsDetailsView = new Paperyard\Views\RulesRecipientsDetailsView($request->getAttribute('id'));
    return $this->view->render($response, 'rules_recipients_details.twig', $rulesRecipientsDetailsView->render());
});

$app->post('/rules/recipients/add', function (Request $request, Response $response, array $args) {
    $ruleRecipients = \Paperyard\RuleRecipients::fromPostValues($request->getParsedBody());
    $ruleRecipients->insert();
    return $response->withRedirect('/rules/recipients');
});

$app->post('/rules/recipients/{id}/delete', function (Request $request, Response $response, array $args) {
    $ruleRecipients =  \Paperyard\RuleRecipients::fromId($request->getAttribute('id'));
    $ruleRecipients->delete();
    return $response->withRedirect('/rules/recipients');
});

$app->post('/rules/recipients/{id}/save', function (Request $request, Response $response, array $args) {
    $ruleRecipients = \Paperyard\RuleRecipients::fromPostValues($request->getParsedBody());
    $ruleRecipients->update($request->getAttribute('id'));
    return $response->withRedirect('/rules/recipients');
});