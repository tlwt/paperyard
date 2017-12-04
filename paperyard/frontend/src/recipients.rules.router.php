<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/rules/recipients', \Paperyard\Controllers\Rule\Recipients::class);

$app->get('/rules/recipients/{ruleId}', \Paperyard\Controllers\Rule\RecipientDetails::class);

$app->post('/rules/recipients/add', function (Request $request, Response $response, array $args) {

    // create new model and fill with data from post request
    $rule = new \Paperyard\Models\Rule\Recipient($request->getParsedBody());

    // validate and save to db if passed
    $rule->validateAndSave();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to overview
    return $response->withRedirect('/rules/recipients');
});

$app->post('/rules/recipients/delete', function (Request $request, Response $response, array $args) {

    // find model from id
    $rules_removed = \Paperyard\Models\Rule\Recipient::destroy((int)$request->getParsedBody()['ruleId']);

    // if not found, add error
    if ($rules_removed < 1) {
        $this->flash->addMessage('error', _("Rule not found"));
    }

    // redirect to overview
    return $response->withRedirect('/rules/recipients');
});

$app->post('/rules/recipients/save', function (Request $request, Response $response, array $args) {

    // find model from id
    $rule = \Paperyard\Models\Rule\Recipient::find((int)$request->getParsedBody()['ruleId']);

    // if not found, redirect with error
    if ($rule === null) {
        $this->flash->addMessage('error', _("Rule not found"));
        return $response->withRedirect('/rules/recipients');
    }

    // overwrite model with data
    $rule->fill($request->getParsedBody());

    // validate and update if passed
    $rule->validateAndUpdate();

    // add potential validation errors
    $this->flash->addMessages('error', $rule->errors);

    // redirect to updated rule
    return $response->withRedirect('/rules/recipients/' . $rule->id);
});
