<?php

namespace Paperyard\Views;

use Paperyard\BasicView;
use Paperyard\RuleRecipients;

/**
 * Class RulesSendersDetailsView
 *
 * Loads information about a rule and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class RulesRecipientsDetailsView extends BasicView
{
    /** @var RuleRecipients object of current rule */
    private $_rule;

    public function __construct($ruleId)
    {
        // call parent for db init
        parent::__construct();

        // breadcrumbs as array of array [Text, URL]
        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Recipients"), ""],
            [_("Detail"), ""]
        );

        // load all rule details from id
        $this->_rule = RuleRecipients::fromId($ruleId);
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