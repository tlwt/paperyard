<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesSubjectsView extends BasicView
{

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Subjects"];
        $this->pageScript = "rules_subjects";
    }

    public function render()
    {
        return array(
            "breadcrumbs" => $this->breadcrumbs,
            "pageScript" => $this->pageScript,
            "rules" => $this->getSubjectRules()
        );
    }

    private function getSubjectRules()
    {
        $results = $this->db->query("SELECT * FROM rule_subjects ORDER BY isActive DESC, foundWords ASC");
        $rows = array();
        while ($row = $results->fetchArray(1)) {
            array_push($rows, $row);
        }
        return $rows ? $rows : [];
    }

}