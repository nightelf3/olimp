<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 20:25
 */
namespace helpers;

class ErrorHelper extends BaseHelper
{
    protected static $isDev = false;

    public static function initialize()
    {
        self::$isDev = ConfigHelper::isDev();
    }

    /**
     * Throw assertion if $condition false
     *
     * @param bool|string $condition
     * @param string $message
     * @throws \Exception
     */
    public static function assert($condition, $message = '')
    {
        if (is_string($condition)) {
            $message = $condition;
            $condition = false;
        }

        if (self::$isDev && !$condition) {
            throw new \Exception($message);
        }
    }

    public static function trace()
    {
        if (self::$isDev) {
            //TODO: \Kint::trace() better?
            throw new \Exception();
        }
    }
}
