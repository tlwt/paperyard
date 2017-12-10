<?php

namespace Paperyard\Helpers;

class ApplicationVersion
{
    const MAJOR = 0;
    const MINOR = 2;

    public static function get()
    {
        $commit_count = file_get_contents('/data/version');

        return sprintf('v%s.%s.%s', self::MAJOR, self::MINOR, $commit_count);
    }
}