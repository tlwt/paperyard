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

$app->get('/docs/archive[/{path:.*}]', function (Request $request, Response $response, array $args) {
    $archiveDocumentsView = new Paperyard\Views\ArchiveDocumentsView($request->getAttribute('path'));
    return $this->view->render($response, 'documents_archive.twig', $archiveDocumentsView->render());
});

$app->get('/docs/{path}', function (Request $request, Response $response, array $args) {
    $archiveDocumentsDetailsView = new Paperyard\Views\ArchiveDocumentsDetailsView(base64_decode($request->getAttribute('path')));
    return $this->view->render($response, 'document_detail.twig', $archiveDocumentsDetailsView->render());
});

$app->get('/thumbnail/{path}', function (Request $request, Response $response, array $args) {
    $im = new imagick(base64_decode($request->getAttribute('path')) . '[0]');
    $im->setImageFormat('jpg');
    $newResponse = $response->withHeader('Content-type', 'application/jpeg');
    echo $im;
    $im->destroy();
    return $newResponse;
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

include "routes_rules_senders.php";

include "routes_rules_subjects.php";

include "routes_rules_recipients.php";

include "routes_rules_archive.php";

include "routes_log.php";