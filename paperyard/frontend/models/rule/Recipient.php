<?php

namespace Paperyard\Models\Rule;

class Recipient extends Rule
{
    /** @var string associated table */
    protected $table = 'rule_recipients';

    /** @var array mass fillable fields */
    protected $fillable = ['recipientName', 'shortNameForFile', 'isActive'];

    /** @var array validation rules for fields */
    protected $rules = [
        'required' => [
            ['recipientName'],
            ['shortNameForFile'],
            ['isActive']
        ],
        'alphaNum' => [
            ['recipientName'],
            ['shortNameForFile']
        ]
    ];

    protected $labels = [
        'recipientName' => 'Long Name',
        'shortNameForFile' => 'Name For File',
        'isActive' => 'Status'
    ];
}