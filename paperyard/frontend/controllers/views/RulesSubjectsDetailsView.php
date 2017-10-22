<?php

namespace Paperyard\Views;

use Paperyard\BasicView;
use Paperyard\RuleSubjects;

/**
 * Class RulesSubjectsDetailsView
 *
 * Loads information about a rule and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class RulesSubjectsDetailsView extends BasicView
{
    /** @var RuleSubjects object of current rule */
    private $_rule;

    public function __construct($ruleId)
    {
        // call parent for db init
        parent::__construct();

        // breadcrumbs as array of array [Text, URL]
        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Subjects"), ""],
            [_("Detail"), ""]
        );

        // load all rule details from id
        $this->_rule = RuleSubjects::fromId($ruleId);
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