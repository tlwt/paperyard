<?php

namespace Paperyard\Views;


class RulesSendersView extends \Paperyard\BasicView
{

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Senders"];
        $this->pageScript = "rules_senders";
    }

    public function render()
    {
        return array(
            "breadcrumbs" => $this->breadcrumbs,
            "pageScript" => $this->pageScript,
            "senders" => $this->getRulesSenders()
        );
    }

    private function getRulesSenders()
    {
        $results = $this->db->query("SELECT * FROM rule_senders ORDER BY isActive DESC, foundWords ASC");
        $rows = array();
        while ($row = $results->fetchArray(1)) {
            array_push($rows, $row);
        }
        return $rows ? $rows : [];
    }

}