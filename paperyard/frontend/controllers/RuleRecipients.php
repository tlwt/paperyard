<?php

namespace Paperyard;

class RuleRecipients extends BasicController implements iRule
{
    /** @var int set if already in database */
    private $_id;
    /** @var string name to search for in file */
    private $_recipientName;
    /** @var string (short) version of long name for filename  */
    private $_shortNameForFile;
    /** @var bool indication of rule shall be applied or not */
    private $_isActive;

    public static function fromId($ruleId) {
        $instance = new self();
        $instance->_id = $ruleId;
        $instance->load();

        return $instance;
    }

    public static function fromPostValues($postValues) {
        $instance = new self();
        $instance->_recipientName = $postValues['recipientNameInput'];
        $instance->_shortNameForFile = $postValues['shortNameForFileInput'];
        $instance->_isActive = isset($postValues['activeInput']);

        return $instance;
    }

    public function toArray() {
        return array(
            "id" => $this->_id,
            "recipientName" => $this->_recipientName,
            "shortNameForFile" => $this->_shortNameForFile,
            "isActive" => $this->_isActive
        );
    }

    public function insert()
    {
        # check if array is not empty, return error codes
        $validationResult = $this->validateData();
        if ($validationResult !== []) {
            var_dump($validationResult);
        }

        # check passed, write to db
        $statement = $this->db->prepare('INSERT INTO rule_recipients(recipientName, shortNameForFile, isActive) VALUES (:recipientName, :shortNameForFile, :isActive)');
        $statement->bindValue(':recipientName', $this->_recipientName, SQLITE3_TEXT);
        $statement->bindValue(':shortNameForFile', $this->_shortNameForFile, SQLITE3_TEXT);
        $statement->bindValue(':isActive', $this->_isActive, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function update($id)
    {
        # no id from post values
        $this->_id = $id;

        # array empty means no errors
        $validationResult = $this->validateData();
        if ($validationResult !== []) {
            return $validationResult;
        }

        # just overwrite all with the maybe new value
        $statement = $this->db->prepare('UPDATE rule_recipients SET recipientName = :recipientName, shortNameForFile = :shortNameForFile, isActive = :isActive WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->bindValue(':recipientName', $this->_recipientName, SQLITE3_TEXT);
        $statement->bindValue(':shortNameForFile', $this->_shortNameForFile, SQLITE3_TEXT);
        $statement->bindValue(':isActive', $this->_isActive, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function delete()
    {
        # check if _id is even set
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('DELETE FROM rule_recipients WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->execute();
    }

    private function load() {
        # _id has to be set in order to load from database
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('SELECT * FROM rule_recipients WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $result = $statement->execute();
        $row = $result->fetchArray();

        $this->_recipientName = $row['recipientName'];
        $this->_shortNameForFile = $row['shortNameForFile'];
        $this->_isActive = $row['isActive'];
    }

    private function validateData()
    {
        # empty array to store potential error codes
        $errorCodes = [];

        # we actually only realy care about companyScore and isActive, as these are non-string
        if ($this->_recipientName == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if ($this->_shortNameForFile == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }

        # isActive will be set due to isset()

        return $errorCodes;
    }
}