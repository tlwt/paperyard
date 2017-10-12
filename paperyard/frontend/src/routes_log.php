<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/shell-log', function (Request $request, Response $response, array $args) {
    $shellLogView = new Paperyard\Views\ShellLogView();
    return $this->view->render($response, 'shell_log.twig', $shellLogView->render());
});

$app->get('/shell-log/{count}[/{since}]', function (Request $request, Response $response, array $args) {
    $shellLogView = new Paperyard\Views\ShellLogView();
    $count = (int)$request->getAttribute('count');
    $since = (int)$request->getAttribute('since');
    return $response->withJson($shellLogView->get($count, $since));
});