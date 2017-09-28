<?php
/**
 * Created by PhpStorm.
 * User: jannik
 * Date: 27.09.17
 * Time: 03:23
 */

namespace Paperyard;

/**
 * Class BasicController
 *
 * Basically provides direct sqlite3 access for all child classes.
 *
 * @package Paperyard
 */
class BasicController
{
    /** @var \SQLite3 instance to access database */
    protected $db;

    public function __construct()
    {
        $this->db = new \SQLite3("/data/database/paperyard.sqlite");
    }
}