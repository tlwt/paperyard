<?php

namespace Paperyard\Controllers;

class BasicController
{
    protected $view;
    protected $logger;
    protected $flash;

    private $plugins;

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function registerPlugin($name)
    {
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/frontend/public/static/js/plugins/' . $name . '.js')) {
            $this->plugins[] = $name;
        } else {
            $this->logger->warning('cant find plugin: ' . $name . ' for ' . get_called_class());
        }
    }

    public function getLanguageFlag() {
        $codes = array(
            "de_DE" => "flag-icon-de",
            "en_US" => "flag-icon-gb");
        return $codes[$_SESSION['lang-code']];
    }
}
