<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesArchiveView extends BasicView
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Archive"];
        $this->pageScript = "rules_archive";
    }

    public function render()
    {
        return array(
            "breadcrumbs" => $this->breadcrumbs,
            "pageScript" => $this->pageScript,
            "rules" => $this->getArchiveRules()
        );
    }

    private function getArchiveRules()
    {
        $results = $this->db->query("SELECT * FROM rule_archive ORDER BY isActive DESC, toFolder ASC");
        $rows = array();
        while ($row = $results->fetchArray(1)) {
            array_push($rows, $row);
        }
        return $rows ? $rows : [];
    }
}