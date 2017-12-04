<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/latest', \Paperyard\Controllers\Archive\Confirm::class);

$app->get('/latest/{path}', \Paperyard\Controllers\Archive\ConfirmDetails::class);

$app->post('/latest/confirm', function(Request $request, Response $response, $args) {

    //todo: cleanup!

    // we encoded the path in case any special character is used
    $fullpath = base64_decode($request->getParsedBody()['document-path']);

    // test if document exists
    if (!file_exists($fullpath)) {
        $this->flash->addMessage('error', _('Document not found.'));
        return $response->withRedirect('/latest');
    }

    $document = new \Paperyard\Models\Document($fullpath);
    $errors = $document->fill($request->getParsedBody());

    if ($errors !== true) {
        $this->flash->addMessages('error', $errors);
        return $response->withRedirect('/latest/' . $document->identifier);
    }

    $document->confirm();
    $document->save();

    // get files as strings from filesystem
    $outbox = glob("/data/outbox/*.pdf", GLOB_NOSORT);
    $inbox = glob("/data/inbox/*.pdf", GLOB_NOSORT);

    $pdfs = array_merge($outbox, $inbox);

    array_walk($pdfs, function (&$pdf) {
        $pdf = (new \Paperyard\Models\Document($pdf))->toArray();
    });

    $pdfs = array_filter($pdfs, function ($pdf) {
        return !$pdf['isConfirmed'];
    });

    if (!empty($pdfs)) {
        $item = current($pdfs);
        return $response->withRedirect('/latest/' . $item['identifier']);
    }

    return $response->withRedirect('/latest');
});