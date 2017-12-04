<?php

namespace Paperyard\Controllers\Misc;

use Paperyard\Models\Document;
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
        $path = base64_decode($request->getAttribute('path'));
        $page = (int)$request->getAttribute('page');
        $resolution = (int)$request->getAttribute('resolution');

        // set default resolution
        if ($resolution == 0) {
            $resolution = 72;
        }

        // build
        $document_hash = (new Document($path))->hash;
        $thumbnail_path = 'static/img/cache/' . $document_hash . '[' . $page . '][' . $resolution . '].jpg';

        // check if image is in cache
        if (file_exists($thumbnail_path)) {
            $cached_thumbnail_response = $response->withHeader('Content-type', 'image/jpeg');
            readfile($thumbnail_path);
            return $cached_thumbnail_response;
        }

        // load page and cache to disk
        $im = new Imagick();
        $im->setResolution($resolution, $resolution);
        $im->readImage($path . '[' . $page . ']');
        $im->setImageFormat('jpg');
        $im->writeImage($thumbnail_path);

        // add content type and output image data
        $thumbnail_response = $response->withHeader('Content-type', 'image/jpeg');
        echo $im;

        // free memory and return response
        $im->clear();
        $im->destroy();
        return $thumbnail_response;
    }

}