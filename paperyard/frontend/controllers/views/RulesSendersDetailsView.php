<?php

namespace Paperyard\Views;

/**
 * Class RulesSendersDetailsView
 *
 * Loads information about a rule and renders it.
 *
 * @package Paperyard\Views
 */
class RulesSendersDetailsView extends \Paperyard\BasicView
{
    /** @var int id of the current rule */
    private $rule;

    public function __construct($ruleId)
    {
        # call parent for db init
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Senders", "Detail"];
        $this->rule = \Paperyard\RuleSenders::fromId($ruleId);
    }

    public function render() {
        return array(
            "breadcrumbs" => $this->breadcrumbs,
            "rule" => $this->rule->toArray()
        );
    }
}