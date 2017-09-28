<?php

namespace Paperyard;

class RulesArchive implements iRule
{
    /** @var int set if already in database */
    private $_id;
    /** @var string (comma separated) word(s) to search for */
    private $_toFolder;
    /** @var string company to search for */
    private $_company;
    /** @var string the subject to match against, if word(s) AND company are found  */
    private $_subject;
    /** @var int temporary naming score if matched */
    private $_recipient;
    /** @var string (comma separated) tag(s) to add to file if matched */
    private $_tags;
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
        $instance->_toFolder = $postValues['toFolderInput'];
        $instance->_company = $postValues['companyInput'];
        $instance->_subject = $postValues['subjectInput'];
        $instance->_recipient = (int)$postValues['recipientInput'];
        $instance->_tags = $postValues['tagsInput'];
        $instance->_isActive = isset($postValues['activeInput']);

        return $instance;
    }

    public function toArray() {
        return array(
            "id" => $this->_id,
            "toFolder" => $this->_toFolder,
            "company" => $this->_company,
            "subject" => $this->_subject,
            "recipient" => $this->_recipient,
            "tags" => $this->_tags,
            "isActive" => $this->_isActive
        );
    }

    public function insert()
    {
        # check if array is not empty, return error codes
        $validationResult = $this->validateData();
        if ($validationResult !== []) {
            return $validationResult;
        }

        # check passed, write to db
        $statement = $this->db->prepare('INSERT INTO rule_archive(toFolder, company, subject, recipient, tags, isActive) VALUES (:toFolder, :company, :subject, :recipient, :tags, :isActive)');
        $statement->bindValue(':toFolder', $this->_toFolder, SQLITE3_TEXT);
        $statement->bindValue(':company', $this->_company, SQLITE3_TEXT);
        $statement->bindValue(':subject', $this->_subject, SQLITE3_TEXT);
        $statement->bindValue(':recipient', $this->_recipient, SQLITE3_INTEGER);
        $statement->bindValue(':tags', $this->_tags, SQLITE3_TEXT);
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
            var_dump($validationResult);
            exit;
            return $validationResult;
        }

        # just overwrite all with the maybe new value
        $statement = $this->db->prepare('UPDATE rule_archive SET toFolder = :toFolder, company = :company, subject = :subject, recipient = :recipient, tags = :tags, isActive = :isActive WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->bindValue(':toFolder', $this->_toFolder, SQLITE3_TEXT);
        $statement->bindValue(':company', $this->_company, SQLITE3_TEXT);
        $statement->bindValue(':subject', $this->_subject, SQLITE3_TEXT);
        $statement->bindValue(':recipient', $this->_recipient, SQLITE3_INTEGER);
        $statement->bindValue(':tags', $this->_tags, SQLITE3_TEXT);
        $statement->bindValue(':isActive', $this->_isActive, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function delete()
    {
        # check if _id is even set
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('DELETE FROM rule_archive WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->execute();
    }

    private function load() {
        # _id has to be set in order to load from database
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('SELECT * FROM rule_archive WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $result = $statement->execute();
        $row = $result->fetchArray();

        $this->_toFolder = $row['toFolder'];
        $this->_company = $row['company'];
        $this->_subject = $row['subject'];
        $this->_recipient = $row['recipient'];
        $this->_tags = $row['tags'];
        $this->_isActive = $row['isActive'];
    }

    private function validateData()
    {
        # empty array to store potential error codes
        $errorCodes = [];

        # basic empty checking
        if ($this->_toFolder == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if ($this->_company == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if ($this->_subject == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if ($this->_recipient == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if ($this->_tags == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }

        # isActive will be set due to isset()

        return $errorCodes;
    }
}