<?php

use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/shell', Paperyard\Controllers\Misc\Shell::class);

$app->get('/shell/{count}[/{since}]', Paperyard\Controllers\Misc\Shell::class);