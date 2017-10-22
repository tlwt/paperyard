<?php

namespace Paperyard\Views;

use Paperyard\BasicView;


class RulesArchiveView extends BasicView
{

    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = array(
            [_("Rules"), ""],
            [_("Archive"), ""]
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
            "rules" => $this->getArchiveRules()
        );
    }

    /**
     * getArchiveRules
     *
     * Load all rules sorted by name and status from database.
     *
     * @return array
     */
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