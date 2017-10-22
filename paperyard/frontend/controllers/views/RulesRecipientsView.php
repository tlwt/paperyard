<?php

namespace Paperyard\Views;

use Paperyard\BasicView;

class RulesRecipientsView extends BasicView
{

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Recipients"), ""]
        );
        $this->plugins = ["clickable-row"];
    }

    /**
     * render
     *
     * @return array data to render the view
     */
    public function render()
    {
        return array(
            "parent" => parent::render(),
            "rules" => $this->getRecipientRules()
        );
    }

    /**
     * getRecipientRules
     *
     * Load all rules sorted by name and status from database.
     *
     * @return array
     */
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