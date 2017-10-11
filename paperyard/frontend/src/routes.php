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

$app->post('/setlang', function (Request $request, Response $response, array $args) {
    $langCode = $request->getParsedBody()['code'];
    $supportedCodes = array_map(function ($code) { return basename($code); }, glob("../locale/*", GLOB_ONLYDIR));
    if (in_array($langCode, $supportedCodes)) {
        $_SESSION["lang-code"] = $langCode;
        return $response;
    }
    return $response->withStatus(406);
});

include "routes_senders.php";

include "routes_subjects.php";

include "routes_recipients.php";

include "routes_archive.php";