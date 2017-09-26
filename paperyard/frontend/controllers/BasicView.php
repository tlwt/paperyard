<?php

namespace Paperyard;

class BasicView
{
    protected $db;
    protected $breadcrumbs;
    protected $pageScript;

    public function __construct()
    {
        $this->db = new \SQLite3("/data/database/paperyard.sqlite");
    }
}