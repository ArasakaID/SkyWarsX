<?php

namespace vixikhd\skywars\math;

class Vector3 extends \pocketmine\math\Vector3
{

    public function __toString(): string
    {
        return "$this->x,$this->y,$this->z";
    }

    public static function fromString(string $string): Vector3
    {
        return new Vector3((int)explode(",", $string)[0], (int)explode(",", $string)[1], (int)explode(",", $string)[2]);
    }
}