<?php

namespace Paperyard\Views;

class IndexView extends \Paperyard\BasicView
{
    public function __construct()
    {
        parent::__construct();

        $this->breadcrumbs = [];
    }

    public function render()
    {
        return array(
            "breadcrumb" => $this->breadcrumbs,
            "scannedToday" => $this->documentsScannedToday()
        );
    }

    private function documentsScannedToday() {
        $results = $this->db->query("SELECT count(*) AS documentCount FROM (SELECT DISTINCT newFileName, fileContent FROM logs)");
        $row = $results->fetchArray();
        return $row['documentCount'];
    }

}