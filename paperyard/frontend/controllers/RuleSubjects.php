<?php

namespace Paperyard;

class RuleSubjects extends BasicController implements iRule
{
    /** @var int set if already in database */
    private $_id;
    /** @var string (comma separated) word(s) to search for */
    private $_foundWords;
    /** @var string company to search for */
    private $_foundCompany;
    /** @var string the subject to match against, if word(s) AND company are found  */
    private $_fileSubject;
    /** @var int temporary naming score if matched */
    private $_subjectScore;
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
        $instance->_foundCompany = $postValues['foundCompanyInput'];
        $instance->_fileSubject = $postValues['fileSubjectInput'];
        $instance->_subjectScore = (int)$postValues['subjectScoreInput'];
        $instance->_tags = $postValues['tagsInput'];
        $instance->_isActive = isset($postValues['activeInput']);

        return $instance;
    }

    public function toArray() {
        return array(
            "id" => $this->_id,
            "foundWords" => $this->_foundWords,
            "foundCompany" => $this->_foundCompany,
            "fileSubject" => $this->_fileSubject,
            "subjectScore" => $this->_subjectScore,
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
        $statement = $this->db->prepare('INSERT INTO rule_subjects(foundWords, foundCompany, fileSubject, subjectScore, tags, isActive) VALUES (:foundWords, :foundCompany, :fileSubject, :subjectScore, :tags, :isActive)');
        $statement->bindValue(':foundWords', $this->_foundWords, SQLITE3_TEXT);
        $statement->bindValue(':foundCompany', $this->_foundCompany, SQLITE3_TEXT);
        $statement->bindValue(':fileSubject', $this->_fileSubject, SQLITE3_TEXT);
        $statement->bindValue(':subjectScore', $this->_subjectScore, SQLITE3_INTEGER);
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
        $statement = $this->db->prepare('UPDATE rule_subjects SET foundWords = :foundWords, foundCompany = :foundCompany, fileSubject = :fileSubject, subjectScore = :subjectScore, tags = :tags, isActive = :isActive WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->bindValue(':foundWords', $this->_foundWords, SQLITE3_TEXT);
        $statement->bindValue(':foundCompany', $this->_foundCompany, SQLITE3_TEXT);
        $statement->bindValue(':fileSubject', $this->_fileSubject, SQLITE3_TEXT);
        $statement->bindValue(':subjectScore', $this->_subjectScore, SQLITE3_INTEGER);
        $statement->bindValue(':tags', $this->_tags, SQLITE3_TEXT);
        $statement->bindValue(':isActive', $this->_isActive, SQLITE3_INTEGER);
        $statement->execute();
    }

    public function delete()
    {
        # check if _id is even set
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('DELETE FROM rule_subjects WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $statement->execute();
    }

    private function load() {
        # _id has to be set in order to load from database
        if ($this->_id == null)
            return;

        $statement = $this->db->prepare('SELECT * FROM rule_subjects WHERE id = :id');
        $statement->bindValue(':id', $this->_id, SQLITE3_INTEGER);
        $result = $statement->execute();
        $row = $result->fetchArray();

        $this->_foundWords = $row['foundWords'];
        $this->_foundCompany = $row['foundCompany'];
        $this->_fileSubject = $row['fileSubject'];
        $this->_subjectScore = $row['subjectScore'];
        $this->_tags = $row['tags'];
        $this->_isActive = $row['isActive'];
    }

    private function validateData()
    {
        # empty array to store potential error codes
        $errorCodes = [];

        # we actually only realy care about subjectScore and isActive, as these are non-string
        if (!is_int($this->_subjectScore)) {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }
        if (!is_bool($this->_isActive)) {
            array_push($errorCodes, ErrorCodes::PARAMETER_FORMAT_MISMATCH);
        }

        # basic empty checking should still be made
        if ($this->_foundWords == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_NULL);
        }
        if ($this->_foundCompany == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_NULL);
        }
        if ($this->_fileSubject == "") {
            array_push($errorCodes, ErrorCodes::PARAMETER_NULL);
        }

        return $errorCodes;
    }
}