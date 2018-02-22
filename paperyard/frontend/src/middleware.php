<?php
// Application middleware

use \Slim\Middleware\HttpBasicAuthentication\PdoAuthenticator;

// check if any user exists
if (\Paperyard\Models\User::get()->count() > 0) {

    $pdo = new \PDO('sqlite:/data/database/paperyard.sqlite');

    $app->add(new \Slim\Middleware\HttpBasicAuthentication([
        'path' => ['/'],
        'passthrough' => ['/login', '/shell'],
        'authenticator' => new PdoAuthenticator([
            'pdo' => $pdo
        ])
    ]));

}
