<?php

namespace App\Dto;

abstract class Dto
{

    public function toArray(): array {
        $array = [];
        foreach((array)$this as $property => $value) {
            $property_parts = explode("\0", $property);
            $array[end($property_parts)] = $value;
        }
        return $array;
    }
}
