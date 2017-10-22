<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesRecipientsView extends BasicView
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = array(
            ["Rules", ""],
            ["Recipients", ""]
        );
        $this->plugins = ["clickable-row"];
    }

    public function render()
    {
        return array(
            "parent" => parent::render(),
            "rules" => $this->getRecipientRules()
        );
    }

    private function getRecipientRules()
    {
        $results = $this->db->query("SELECT * FROM rule_recipients ORDER BY isActive DESC, recipientName ASC");
        $rows = array();
        while ($row = $results->fetchArray(1)) {
            array_push($rows, $row);
        }
        return $rows ? $rows : [];
    }
}