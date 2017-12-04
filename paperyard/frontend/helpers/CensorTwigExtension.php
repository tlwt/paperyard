<?php

namespace Paperyard\Helpers;

/**
 * Class CensorTwigExtension
 *
 * Deletes certain values from array (array_diff).
 *
 * @package Paperyard\Helpers
 */
class CensorTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'censor' => new \Twig_SimpleFilter('censor', array($this, 'censor'))
        );
    }

    public function censor($value, $censors, $explode = true)
    {
        // censor array with array
        if (is_array($value) && is_array($censors)) {
            return array_diff($value, $censors);
        }

        // censor string (explode)
        if (!is_array($value) && $explode) {
            $arrayed = explode(',', $value);

            // make array from single string and filter
            $censored = array_diff($arrayed, $censors);

            // check if empty
            if (empty($censored)) {
                return '';
            }

            // return first (and only?) string
            return $censored[0];
        }

        return ($value != $censors) ? $value : '';

    }

    public function getName()
    {
        return 'censor_extention';
    }
}