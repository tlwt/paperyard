<?php

namespace Paperyard;

class RuleSenders extends BasicController implements iRule
{
    /** @var int set if already in database */
    private $_id;
    /** @var string (comma separated) word(s) to search for */
    private $_foundWords;
    /** @var string the company to match against, if word(s) are found  */
    private $_fileCompany;
    /** @var int temporary naming score if matched */
    private $_companyScore;
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
        $instance->_foundWords = $postValues['foundWordsInput'];
        $instance->_fileCompany = $postValues['fileCompanyInput'];
        $instance->_companyScore = (int)$postValues['companyScoreInput'];
        $instance->_tags = $postValues['tagsInput'];
        $instance->_isActive = isset($postValues['activeInput']);

        return $instance;
    }

    public function toArray() {
        return array(
            "id" => $this->_id,
            "foundWords" => $this->_foundWords,
            "fileCompany" => $this->_fileCompany,
            "companyScore" => $this->_companyScore,
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
        $statement = $this->db->prepare('INSERT INTO rule_senders(foundWords, fileCompany, companyScore, tags, isActive) VALUES (:foundWords, :fileCompany, :companyScore, :tags, :isActive)');
        $statement->bindValue(':foundWords', $this->_foundWords, SQLITE3_TEXT);
        $statement->bindValue(':fileCompany', $this->_fileCompany, SQLITE3_TEXT);
        $statement->bindValue(':companyScore', $this->_companyScore, SQLITE3_INTEGER);
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
            return $validationResult;
        }

        # just overwrite all with the maybe new value
        $statement = $this->db->prepare('UPDATE rule_senders SET foundWords = :foundWords, fileCompany = :fileCompany, companyScore = :companyScore, tags = :tags, isActive = :isActive WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->bindValue(':foundWords', $this->_foundWords, SQLITE3_TEXT);
        $statement->bindValue(':fileCompany', $this->_fileCompany, SQLITE3_TEXT);
        $statement->bindValue(':companyScore', $this->_companyScore, SQLITE3_INTEGER);
        $statement->bindValue(':tags', $this->_tags, SQLITE3_TEXT);
        $statement->bindValue(':isActive', $this->_isActive, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function delete()
    {
        # check if _id is even set
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('DELETE FROM rule_senders WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->execute();
    }

    private function load() {
        # _id has to be set in order to load from database
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('SELECT * FROM rule_senders WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $result = $statement->execute();
        $row = $result->fetchArray();

        $this->_foundWords = $row['foundWords'];
        $this->_fileCompany = $row['fileCompany'];
        $this->_companyScore = (int)$row['companyScore'];
        $this->_tags = $row['tags'];
        $this->_isActive = $row['isActive'];
    }

    private function validateData()
    {
        # empty array to store potential error codes
        $errorCodes = [];

        # we actually only realy care about companyScore and isActive, as these are non-string
        if (!is_int($this->_companyScore)) {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if (!is_bool($this->_isActive)) {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }

        # basic empty checking should still be made
        if (empty($this->_foundWords)) {
            array_push($errorCodes, ErrorCodes::PARAMETER_NULL);
        }
        // TODO: for tagging only, company could be empty
        //if (empty($this->fileCompany == "")) {
        //    array_push($errorCodes, ErrorCodes::PARAMETER_NULL);
        //}

        # isActive will be set due to isset()

        return $errorCodes;
    }
}