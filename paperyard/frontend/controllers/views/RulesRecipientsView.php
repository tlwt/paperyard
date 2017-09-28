<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class RulesRecipientsView extends BasicView
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = ["Rules", "Recipients"];
        $this->pageScript = "rules_recipients";
    }

    public function render()
    {
        return array(
            "breadcrumbs" => $this->breadcrumbs,
            "pageScript" => $this->pageScript,
            "recipients" => $this->getRecipientRules()
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