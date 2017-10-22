<?php

namespace Paperyard\Views;

use Paperyard\BasicView;
use Paperyard\RuleArchive;

/**
 * Class RulesSendersDetailsView
 *
 * Loads information about a rule and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class RulesArchiveDetailsView extends BasicView
{
    /** @var RuleArchive object of current rule */
    private $_rule;

    public function __construct($ruleId)
    {
        // call parent for db init
        parent::__construct();

        // breadcrumbs as array of array [Text, URL]
        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Archive"), ""],
            [_("Detail"), ""]
        );

        // load all rule details from id
        $this->_rule = RuleArchive::fromId($ruleId);
    }

    /**
     * render
     *
     * @return array data to render the view
     */
    public function render() {
        return array(
            "parent" => parent::render(),
            "rule" => $this->_rule->toArray()
        );
    }
}