<?php

namespace Paperyard\Views;

use Paperyard\BasicView;

class RulesSubjectsView extends BasicView
{

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Subjects"), ""]
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
            "rules" => $this->getSubjectRules()
        );
    }

    /**
     * getSubjectRules
     *
     * Load all rules sorted by name and status from database.
     *
     * @return array
     */
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