<?php

namespace Paperyard\Helpers;

/**
 * Class CapTwigExtension
 *
 * Limits a number to a certain value.
 *
 * @package Paperyard\Helpers
 */
class CapTwigExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array(
            'cap' => new \Twig_SimpleFilter('cap', array($this, 'cap'))
        );
    }

    public function cap($value, $cap)
    {
        return ($value > $cap ? $cap : $value);
    }

    public function getName()
    {
        return 'cap_extention';
    }
}