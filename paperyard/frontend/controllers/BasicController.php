<?php

namespace Paperyard\Controllers;

use Paperyard\Helpers\Enums\PluginType;

class BasicController
{
    /** @var \Slim\Views\Twig */
    protected $view;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var \Slim\Flash\Messages */
    protected $flash;

    /** @var array list of all js plugins to be included in view */
    private $plugins_js;

    /** @var array list of all css plugins to be included in view */
    private $plugins_css;

    /**
     * return successfully registered plugins
     *
     * @return array plugins which exist
     */
    public function getPlugins()
    {
        return array(
            'css' => $this->plugins_css,
            'js' => $this->plugins_js
        );
    }

    /**
     * checks if plugin exist and adds it to the plugin list
     *
     * @param $name string plugin to search for in plugin directory
     * @param $type int flag to toggle plugin type
     */
    public function registerPlugin($name, $type = PluginType::NORMAL)
    {
        $js_path = $_SERVER["DOCUMENT_ROOT"] . '/frontend/public/static/js/plugins/' . $name . '.js';
        $css_path = $_SERVER["DOCUMENT_ROOT"] . '/frontend/public/static/js/plugins/' . $name . '.js';

        switch ($type) {
            case PluginType::NORMAL:
                if (file_exists($js_path) &&
                    file_exists($css_path)) {
                    $this->plugins_js[] = $name;
                    $this->plugins_css[] = $name;
                }
                break;
            case PluginType::ONLY_CSS:
                if (file_exists($css_path)) {
                    $this->plugins_css[] = $name;
                }
                break;
            case PluginType::ONLY_JS:
                if (file_exists($js_path)) {
                    $this->plugins_js[] = $name;
                }
                break;
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
