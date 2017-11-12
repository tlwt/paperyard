<?php

namespace Paperyard\Models\Rule;

class Senders extends Rule
{
    /** @var string associated table */
    protected $table = 'rule_senders';

    /** @var array mass fillable fields */
    protected $fillable = ['foundWords', 'fileCompany', 'companyScore', 'tags', 'isActive'];

    /** @var array validation rules for fields */
    protected $rules = [
        'required' => [
            ['foundWords'],
            ['fileCompany'],
            ['companyScore']
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