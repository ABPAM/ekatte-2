<?php

namespace Utilities;

class EkatteArray
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        //
    }

    /**
     * Връща последния елемент от масив $arr, без да афектира самия масив
     * 
     * @param array $arr
     * @return array
     */
    public static function getlastElem($arr)
    {
        return end($arr);
    }
}