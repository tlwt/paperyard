<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/subjects', Paperyard\Controllers\Rule\Subjects::class);

$app->get('/rules/subjects/{ruleId}', Paperyard\Controllers\Rule\SubjectDetails::class);

$app->post('/rules/subjects/add', function (Request $request, Response $response, array $args) {

    // create new model and fill with data from post request
    $rule = new \Paperyard\Models\Rule\Subjects($request->getParsedBody());

    // validate and save to db if passed
    $rule->validateAndSave();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to overview
    return $response->withRedirect('/rules/subjects');
});

$app->post('/rules/subjects/delete', function (Request $request, Response $response, array $args) {

    // find model from id
    $rules_removed = \Paperyard\Models\Rule\Subjects::destroy((int)$request->getParsedBody()['ruleId']);

    // if not found, add error
    if ($rules_removed < 1) {
        $this->flash->addMessage('error', _("Rule not found"));
    }

    // redirect to overview
    return $response->withRedirect('/rules/subjects');
});

$app->post('/rules/subjects/save', function (Request $request, Response $response, array $args) {

    // find model from id
    $rule = \Paperyard\Models\Rule\Subjects::find((int)$request->getParsedBody()['ruleId']);

    // if not found, redirect with error
    if ($rule === null) {
        $this->flash->addMessage('error', _("Rule not found"));
        return $response->withRedirect('/rules/subjects');
    }

    // overwrite model with data
    $rule->fill($request->getParsedBody());

    // validate and update if passed
    $rule->validateAndUpdate();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to updated rule
    return $response->withRedirect('/rules/subjects/' . $rule->id);
});
