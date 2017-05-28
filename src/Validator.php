<?php

namespace BtTable;


class Validator
{
    const MIN_LENGTH = "isMinLength";
    const MAX_LENGTH = "isMaxLength";
    const EQUAL = "isEqual";


    public static function isMinLength($string, $value)
    {
        return strlen($string) >= $value;
    }


    public static function isMaxLength($string, $value)
    {
        return strlen($string) <= $value;
    }


    public static function isEqual($string, $value)
    {
        return $string == $value;
    }


}