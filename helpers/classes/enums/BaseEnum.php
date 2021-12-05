<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 16:17
 */
namespace helpers\classes\enums;

use helpers\ErrorHelper;

abstract class BaseEnum
{
    final public function __construct($value)
    {
        $c = new \ReflectionClass($this);
        if (!in_array($value, $c->getConstants())) {
            ErrorHelper::assert("{$value} not a const in enum");
        }
        $this->value = $value;
    }

    final public function __toString()
    {
        return "{$this->value}";
    }
}
