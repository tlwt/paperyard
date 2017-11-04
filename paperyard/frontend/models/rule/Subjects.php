<?php

namespace Paperyard\Models\Rule;

class Subjects extends Rule
{
    /** @var string associated table */
    protected $table = 'rule_subjects';

    /** @var array mass fillable fields */
    protected $fillable = ['foundWords', 'foundCompany', 'fileSubject', 'subjectScore', 'tags', 'isActive'];

    /** @var array validation rules for fields */
    protected $rules = [
        'required' => [
            ['foundWords'],
            ['fileSubject'],
            ['subjectScore']
        ],
        'regex' => [
            ['foundWords', parent::TAG_REGEX],
            ['foundCompany', parent::TAG_REGEX],
            ['fileSubject', parent::TAG_REGEX],
            ['tags', parent::TAG_REGEX]
        ],
        'integer' => [
            ['subjectScore']
        ]
    ];

    protected $labels = [
    ];
}