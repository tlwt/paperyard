<?php

namespace Paperyard\Controllers\Misc;

use Psr\Log\LoggerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Imagick;

class Thumbnail
{
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $im = new imagick(base64_decode($request->getAttribute('path')) . '[0]');
        $im->setImageFormat('jpg');
        $newResponse = $response->withHeader('Content-type', 'application/jpeg');
        echo $im;
        $im->destroy();
        return $newResponse;
    }

}