<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/archives', \Paperyard\Controllers\Rule\Archives::class);

$app->get('/rules/archives/{ruleId}', \Paperyard\Controllers\Rule\ArchiveDetails::class);

$app->post('/rules/archives/add', function (Request $request, Response $response, array $args) {

    // create new model and fill with data from post request
    $rule = new \Paperyard\Models\Rule\Archive($request->getParsedBody());

    // validate and save to db if passed
    $rule->validateAndSave();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to overview
    return $response->withRedirect('/rules/archives');
});

$app->post('/rules/archives/delete', function (Request $request, Response $response, array $args) {

    // find model from id
    $rules_removed = \Paperyard\Models\Rule\Archive::destroy((int)$request->getParsedBody()['ruleId']);

    // if not found, add error
    if ($rules_removed < 1) {
        $this->flash->addMessage('error', _("Rule not found"));
    }

    // redirect to overview
    return $response->withRedirect('/rules/archives');
});

$app->post('/rules/archives/save', function (Request $request, Response $response, array $args) {

    // find model from id
    $rule = \Paperyard\Models\Rule\Archive::find((int)$request->getParsedBody()['ruleId']);

    // if not found, redirect with error
    if ($rule === null) {
        $this->flash->addMessage('error', _("Rule not found"));
        return $response->withRedirect('/rules/archives');
    }

    // overwrite model with data
    $rule->fill($request->getParsedBody());

    // validate and update if passed
    $rule->validateAndUpdate();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to updated rule
    return $response->withRedirect('/rules/archives/' . $rule->id);
});
