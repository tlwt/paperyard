<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

/**
 * Class RulesSubjectsDetailsView
 *
 * Loads information about a rule and renders it.
 *
 * @package Paperyard\Views
 */
class RulesSubjectsDetailsView extends BasicView
{
    /** @var int id of the current rule */
    private $_rule;

    public function __construct($ruleId)
    {
        # call parent for db init
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Subjects", "Detail"];
        $this->_subject = \Paperyard\RuleSubjects::fromId($ruleId);
    }

    public function render() {
        return array(
            "parent" => parent::render(),
            "rule" => $this->_subject->toArray()
        );
    }
}