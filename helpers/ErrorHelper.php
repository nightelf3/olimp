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

        if (ConfigHelper::isDebug() && !$condition) {
            throw new \Exception($message);
        }
    }

    public static function trace()
    {
        if (ConfigHelper::isDebug()) {
            //TODO: \Kint::trace() better?
            throw new \Exception();
        }
    }
}
