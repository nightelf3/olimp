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
    final public function __construct($value, $default = null)
    {
        $c = new \ReflectionClass($this);
        if (!in_array($value, $c->getConstants(), true)) {
            ErrorHelper::assert(!is_null($default), "{$value} not a const in enum");
            $value = $default ?: $c->getConstants()[0];
        }
        $this->value = $value;
    }

    final public function __toString()
    {
        return "{$this->value}";
    }

    final public function value()
    {
        return $this->value;
    }

    protected $value = null;
}
