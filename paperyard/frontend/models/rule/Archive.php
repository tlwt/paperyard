<?php

namespace Paperyard\Models\Rule;

class Archive extends Rule
{
    /** @var string associated table */
    protected $table = 'rule_archive';

    /** @var array mass fillable fields */
    protected $fillable = ['toFolder', 'company', 'subject', 'recipient', 'tags', 'isActive'];

    /** @var array validation rules for fields */
    protected $rules = [
        'required' => [
            ['toFolder'],
            ['company'],
            ['subject'],
            ['recipient'],
            ['tags']
        ],
        'regex' => [
            ['foundWords', parent::TAG_REGEX],
            ['fileCompany', parent::TAG_REGEX],
            ['tags', parent::TAG_REGEX]
        ],
        'integer' => [
            ['companyScore']
        ]
    ];

    /** @var array maps internal field names to readable labels */
    protected $labels = [
        'foundWords' => 'Needles',
        'fileCompany' => 'Company',
        'companyScore' => 'Score',
        'tags' => 'Tags',
        'isActive' => 'Status'
    ];
}