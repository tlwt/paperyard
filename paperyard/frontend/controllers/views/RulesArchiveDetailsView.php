<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesArchiveDetailsView extends BasicView
{
    /** @var int id of the current rule */
    private $_archive;

    public function __construct($ruleId)
    {
        # call parent for db init
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Archive", "Detail"];
        $this->_archive = \Paperyard\RuleArchive::fromId($ruleId);
    }

    public function render() {
        return array(
            "parent" => parent::render(),
            "rule" => $this->_archive->toArray()
        );
    }
}