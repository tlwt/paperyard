<?php

namespace Paperyard\Controllers;

class BasicController
{
    /** @var \Slim\Views\Twig */
    protected $view;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var \Slim\Flash\Messages */
    protected $flash;

    /** @var array */
    private $plugins;

    /**
     * return successfully registered plugins
     *
     * @return array plugins which exist
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * checks if plugin exist and adds it to the plugin list
     *
     * @param $name string plugin to search for in plugin directory
     */
    public function registerPlugin($name)
    {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/frontend/public/static/js/plugins/' . $name . '.js')) {
            $this->plugins[] = $name;
        } else {
            $this->logger->warning('cant find plugin: ' . $name . ' for ' . get_called_class());
        }
    }

    /**
     * maps current set language to html class
     *
     * @return string
     */
    public function getLanguageFlag() {
        $codes = array(
            "de_DE" => "flag-icon-de",
            "en_US" => "flag-icon-gb");
        return $codes[$_SESSION['lang-code']];
    }
}
