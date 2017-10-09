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

        $this->breadcrumbs = ["Rules", "Recipients", "Detail"];
        $this->_recipient = \Paperyard\RuleRecipients::fromId($ruleId);
    }

    public function render() {
        return array(
            "parent" => parent::render(),
            "recipient" => $this->_recipient->toArray()
        );
    }
}