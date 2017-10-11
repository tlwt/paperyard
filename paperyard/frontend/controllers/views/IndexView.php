<?php

namespace Paperyard\Views;

/**
 * Class IndexView
 *
 * Loads dashboard informations and provide it to through the render function.
 *
 * @package Paperyard\Views
 */
class IndexView extends \Paperyard\BasicView
{
    public function __construct()
    {
        # call parent for db init
        parent::__construct();

        # index view, no breadcrumbs
        $this->breadcrumbs = [];
    }

    public function render()
    {
        return array(
            "parent" => parent::render(),
            "scannedToday" => $this->documentsScanned()
        );
    }

    private function documentsScanned() {
        $results = $this->db->query("SELECT count(*) AS documentCount FROM (SELECT DISTINCT newFileName, fileContent FROM logs)");
        $row = $results->fetchArray();
        return $row['documentCount'];
    }

}