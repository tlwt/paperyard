<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/senders', Paperyard\Controllers\Rule\Senders::class);

$app->get('/rules/senders/{ruleId}', Paperyard\Controllers\Rule\SenderDetails::class);

$app->post('/rules/senders/add', function (Request $request, Response $response, array $args) {

    // create new model and fill with data from post request
    $rule = new \Paperyard\Models\Rule\Senders($request->getParsedBody());

    // validate and save to db if passed
    $rule->validateAndSave();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to overview
    return $response->withRedirect('/rules/senders');
});

$app->post('/rules/senders/delete', function (Request $request, Response $response, array $args) {

    // find model from id
    $rules_removed = \Paperyard\Models\Rule\Senders::destroy((int)$request->getParsedBody()['ruleId']);

    // if not found, add error
    if ($rules_removed < 1) {
        $this->flash->addMessage('error', _("Rule not found"));
    }

    // redirect to overview
    return $response->withRedirect('/rules/senders');
});

$app->post('/rules/senders/save', function (Request $request, Response $response, array $args) {

    // find model from id
    $rule = \Paperyard\Models\Rule\Senders::find((int)$request->getParsedBody()['ruleId']);

    // if not found, redirect with error
    if ($rule === null) {
        $this->flash->addMessage('error', _("Rule not found"));
        return $response->withRedirect('/rules/senders');
    }

    // overwrite model with data
    $rule->fill($request->getParsedBody());

    // validate and update if passed
    $rule->validateAndUpdate();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to updated rule
    return $response->withRedirect('/rules/senders/' . $rule->id);
});
