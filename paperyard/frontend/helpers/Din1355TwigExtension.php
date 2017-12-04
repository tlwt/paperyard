<?php

namespace Paperyard\Helpers;

class Din1355TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'din1355' => new \Twig_SimpleFilter('din1355', array($this, 'din1355'))
        );
    }

    public function din1355($value)
    {
        if (is_numeric($value)) {
            return date_format(date_create($value), 'd.m.Y');
        }

        return $value;
    }

    public function getName()
    {
        return 'din1355_extention';
    }

}