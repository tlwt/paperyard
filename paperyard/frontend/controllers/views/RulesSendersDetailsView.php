<?php

namespace Paperyard\Views;

use Paperyard\BasicView;
use Paperyard\RuleSenders;

/**
 * Class RulesSendersDetailsView
 *
 * Loads information about a rule and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class RulesSendersDetailsView extends BasicView
{

    /** @var RuleSenders object of current rule */
    private $_rule;

    public function __construct($ruleId)
    {
        // call parent for db init
        parent::__construct();

        // breadcrumbs as array of array [Text, URL]
        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Senders"), ""],
            [_("Detail"), ""]
        );

        // load all rule details from id
        $this->_rule = RuleSenders::fromId($ruleId);
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