<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesSendersView extends BasicView
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = array(
            ["Rules", ""],
            ["Senders", ""]
        );
        $this->plugins = ["clickable-row"];
    }

    public function render()
    {
        return array(
            "parent" => parent::render(),
            "rules" => $this->getSenderRules()
        );
    }

    private function getSenderRules()
    {
        $results = $this->db->query("SELECT * FROM rule_senders ORDER BY isActive DESC, foundWords ASC");
        $rows = array();
        while ($row = $results->fetchArray(1)) {
            array_push($rows, $row);
        }
        return $rows ? $rows : [];
    }
}