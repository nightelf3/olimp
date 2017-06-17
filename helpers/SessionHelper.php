<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 20:13
 */
namespace helpers;

class SessionHelper extends BaseHelper
{
    /**
     * Get session field
     *
     * @param array ...$keys
     * @return mixed|null
     */
    public static function get(... $keys)
    {
        $filed = $_SESSION;

        foreach ($keys as $key) {
            if (isset($filed)) {
                $filed = $filed[$key];
            } else {
                return null;
            }
        }

        return $filed;
    }
}
