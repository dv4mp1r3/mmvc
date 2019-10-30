<?php

namespace mmvc\models\helpers;

use mmvc\models\BaseModel;

class ColoredConsole extends BaseModel
{
    const COLOR_NO_COLOR = '1;37';
    const COLOR_RED = '0;31';
    const COLOR_GREEN = '0;32';
    const COLOR_YELLOW = '1;33';


    /**
     * @param string $string
     * @param int $color
     */
    public static function printLine(string $string, string $color = '1;37'): void
    {
        echo self::getLine($string, $color);
    }

    public static function getLine(string $string, string $color = '1;37'): string
    {
        return self::paintString($string, $color);
    }

    public static function paintString(string $string, string $color = '1;37'): string
    {
        return "\033[{$color}m{$string}\033[0m";
    }
}