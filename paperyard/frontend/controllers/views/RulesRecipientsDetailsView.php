<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesRecipientsDetailsView extends BasicView
{
    /** @var int id of the current rule */
    private $_recipient;

    public function __construct($ruleId)
    {
        # call parent for db init
        parent::__construct();

        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Recipients"), ""],
            [_("Detail"), ""]
        );
        $this->_recipient = \Paperyard\RuleRecipients::fromId($ruleId);
    }

    public function render() {
        return array(
            "parent" => parent::render(),
            "rule" => $this->_recipient->toArray()
        );
    }
}