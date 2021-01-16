<?php

namespace vixikhd\skywars\math;

class Time
{

    public static function calculateTime(int $time): string
    {
        return gmdate("i:s", $time);
    }
}
