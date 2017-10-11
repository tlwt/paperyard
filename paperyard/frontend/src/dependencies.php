<?php
// DIC configuration

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $settings = $container->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path'], [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    // add multilanguage support
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

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['view']->render($response, '404.twig')->withStatus(404);
    };
};