<?php
// DIC configuration

// get container
$container = $app->getContainer();

// register component on container
$container['view'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => false
    ]);

    // instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
    $view->addExtension(new \Paperyard\Helpers\CapTwigExtension());
    $view->addExtension(new Knlv\Slim\Views\TwigMessages(
        new Slim\Flash\Messages()
    ));

    if ($c->get('settings')['displayErrorDetails']) {
        $view->addExtension(new Twig_Extension_Debug());
    }

    // multi lang support
    $view->addExtension(new Twig_Extensions_Extension_I18n());

    if ($_SESSION["lang-code"] == "") {
        $_SESSION["lang-code"] = "en_US";
    }
    $locale = $_SESSION["lang-code"];
    $locale .= ".UTF-8";
    putenv('LC_ALL='.$locale);
    setlocale(LC_ALL, $locale);
    bindtextdomain("default", '../locale');
    textdomain("default");

    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// register provider
$container['flash'] = function () {
    return new \Paperyard\Helpers\PaperyardMassages();
};

// not found page
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response, '404.twig')->withStatus(404);
    };
};

// index callable
$container[\Paperyard\Controllers\Misc\Index::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Misc\Index($view, $logger, $flash);
};

// shell log callable
$container[\Paperyard\Controllers\Misc\Shell::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Misc\Shell($view, $logger, $flash);
};

// archive callables
$container[\Paperyard\Controllers\Archive\Archive::class] = function ($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Archive\Archive($view, $logger, $flash);
};

$container[\Paperyard\Controllers\Archive\Details::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Archive\Details($view, $logger, $flash);
};

$container[\Paperyard\Controllers\Archive\Confirm::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Archive\Confirm($view, $logger, $flash);
};

// recipient callables
$container[\Paperyard\Controllers\Rule\Recipients::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\Recipients($view, $logger, $flash);
};

$container[\Paperyard\Controllers\Rule\RecipientDetails::class] = function ($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\Recipients($view, $logger, $flash);
};

// sender callables
$container[\Paperyard\Controllers\Rule\Senders::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\Senders($view, $logger, $flash);
};

$container[\Paperyard\Controllers\Rule\SenderDetails::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\SenderDetails($view, $logger, $flash);
};

// subject callables
$container[\Paperyard\Controllers\Rule\Subjects::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\Subjects($view, $logger, $flash);
};

$container[\Paperyard\Controllers\Rule\SubjectDetails::class] = function($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\SubjectDetails($view, $logger, $flash);
};

// archives callables
$container[\Paperyard\Controllers\Rule\Archives::class] = function ($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\Archives($view, $logger, $flash);
};

$container[\Paperyard\Controllers\Rule\ArchiveDetails::class] = function ($c) {
    $view = $c->get('view');
    $logger = $c->get('logger');
    $flash = $c->get('flash');
    return new Paperyard\Controllers\Rule\ArchiveDetails($view, $logger, $flash);
};

// pdf thumbnail generator
$container[\Paperyard\Controllers\Misc\Thumbnail::class] = function($c) {
    $logger = $c->get('logger');
    return new Paperyard\Controllers\Misc\Thumbnail($logger);
};
