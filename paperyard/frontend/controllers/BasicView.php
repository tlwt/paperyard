<?php

namespace Paperyard;

/**
 * Class BasicView
 *
 * Extends BasicController by a few view related vars which are always needed.
 *
 * @package Paperyard
 */
class BasicView extends BasicController
{
    /** @var array|null list if subpages for breadcrumb element on page */
    protected $breadcrumbs;
    /** @var string name of site specific script without path nor .js */
    protected $pageScript;

    public function render() {
        return array(
            "breadcrumbs" => $this->breadcrumbs,
            "pageScript" => $this->pageScript
        );
    }
}