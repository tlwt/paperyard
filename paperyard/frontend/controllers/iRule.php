<?php

namespace Paperyard;

/**
 * Interface iRule
 *
 * Represents a single rule and allows its creation, modification and deletion.
 *
 * @package Paperyard
 */
interface iRule
{
    /**
     * fromId
     *
     * Load a rule from its corresponding id.
     *
     * @param $ruleId int
     * @return mixed instance of the rule
     */
    public static function fromId($ruleId);

    /**
     * fromPostValues
     *
     * Loads the rule instance with data from an post array.
     * Used to either create a new or load an existing rule.
     *
     * @param $postValues array
     * @return mixed
     */
    public static function fromPostValues($postValues);

    /**
     * toArray
     *
     * Return the properties necessary to render the rule.
     *
     * @return array
     */
    public function toArray();

    public function insert();

    public function update($id);

    public function delete();
}