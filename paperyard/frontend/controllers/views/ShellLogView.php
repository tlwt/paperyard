<?php

namespace Paperyard\Views;
use Paperyard\BasicView;

class ShellLogView extends BasicView
{
    public function __construct()
    {
        # call parent for db init
        parent::__construct();

        # index view, no breadcrumbs
        $this->breadcrumbs = ["Extras", "Shell Log"];
        $this->pageScript = "shell-log";
    }

    public function render()
    {
        return array(
            "parent" => parent::render()
        );
    }

    public function get($count, $since) {
        if ($count == 0) {
            $count = 40;
        }
        $statement = $this->db->prepare("SELECT * FROM (SELECT id, logProgram, logContent, created_at FROM logShell WHERE id > :since ORDER BY id DESC LIMIT :count) ORDER BY id ASC");
        $statement->bindValue(":count", $count);
        $statement->bindValue(":since", $since);
        $results = $statement->execute();
        $rows = array();
        while ($row = $results->fetchArray(1)) {
            array_push($rows, $row);
        }
        return $rows;
    }
}