<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

/**
 * Class RulesSendersDetailsView
 *
 * Loads information about a rule and renders it.
 *
 * @package Paperyard\Views
 */
class RulesSendersDetailsView extends BasicView
{
    /** @var int id of the current rule */
    private $_rule;

    public function __construct($ruleId)
    {
        # call parent for db init
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Senders", "Detail"];
        $this->_rule = \Paperyard\RuleSenders::fromId($ruleId);
    }

    public function render() {
        return array(
            "parent" => parent::render(),
            "rule" => $this->_rule->toArray()
        );
    }
}