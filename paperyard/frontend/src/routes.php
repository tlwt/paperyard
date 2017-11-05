<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', Paperyard\Controllers\Misc\Index::class);

$app->get('/archive[/{path:.*}]', Paperyard\Controllers\Archive\Documents::class);

//$app->get('/docs/{path}', function (Request $request, Response $response, array $args) {
//    $archiveDocumentsDetailsView = new Paperyard\Views\ArchiveDocumentsDetailsView(base64_decode($request->getAttribute('path')));
//    return $this->view->render($response, 'document_detail.twig', $archiveDocumentsDetailsView->render());
//});
//
//$app->get('/thumbnail/{path}', function (Request $request, Response $response, array $args) {
//    $im = new imagick(base64_decode($request->getAttribute('path')) . '[0]');
//    $im->setImageFormat('jpg');
//    $newResponse = $response->withHeader('Content-type', 'application/jpeg');
//    echo $im;
//    $im->destroy();
//    return $newResponse;
//});

$app->post('/setlang', function (Request $request, Response $response, array $args) {
    $langCode = $request->getParsedBody()['code'];
    $supportedCodes = array_map(function ($code) { return basename($code); }, glob("../locale/*", GLOB_ONLYDIR));
    if (in_array($langCode, $supportedCodes)) {
        $_SESSION["lang-code"] = $langCode;
        return $response;
    }
    return $response->withStatus(406);
});


include "senders.rules.router.php";

include "recipients.rules.router.php";

include "subjects.rules.router.php";

include "archive.rules.router.php";

include "log.shell.router.php";