<?php

namespace Paperyard\Models\Rule;

use Illuminate\Database\Eloquent\Model;
use Valitron\Validator;

class Rule extends Model
{
    /** matches AlphaNum, umlauts and commas */
    const TAG_REGEX = '/^([ÄäÜüÖöß\sa-zA-Z0-9]+,)*[ÄäÜüÖöß\sa-zA-Z0-9]+$/';

    /** @var array preset by overwrite */
    protected $attributes = array(
        'isActive' => 0
    );

    /** @var array rules to pass */
    protected $rules;

    /** @var array labels for attributes */
    protected $labels;

    /** @var array error if any */
    public $errors = [];

    /**
     * Mutates post data reliable.
     *
     * @param $value value from post
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['isActive'] = (int)($value == 'on');
    }

    /**
     * Checks if data matches rule set.
     */
    private function validate()
    {
        // new validator object
        $validator = new Validator($this->getAttributes());

        // pass rules
        $validator->rules($this->rules);

        // add labels (we dont want to show internal names to the user)
        $validator->labels($this->labels);

        // check rules
        if(!$validator->validate()) {
            $this->errors = $validator->errors();
        }
    }

    /**
     * Saves data if it matches rule set.
     *
     * @return bool
     */
    public function validateAndSave()
    {
        // check rules
        $this->validate();

        // return result
        if(empty($this->errors)) {
            return $this->save();
        } else {
            return false;
        }
    }

    /**
     * Updates data if it matches rule set.
     *
     * @return bool
     */
    public function validateAndUpdate()
    {
        // check rules
        $this->validate();

        // return result
        if(empty($this->errors)) {
            return $this->update();
        } else {
            return false;
        }
    }
}