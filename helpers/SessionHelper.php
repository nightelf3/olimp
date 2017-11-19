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
        $field = $_SESSION;

        foreach ($keys as $key) {
            if (isset($field)) {
                $field = $field[$key];
            } else {
                return null;
            }
        }

        return $field;
    }

    /**
     * Set value in session by key
     *
     * @param $key
     * @param $value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Remove key from session
     *
     * @param array ...$keys
     * @return bool
     */
    public static function remove(... $keys)
    {
        $field = &$_SESSION;

        foreach ($keys as $key) {
            if (isset($field)) {
                if ($key == end($keys)) {
                    unset($field[$key]);
                } else {
                    $field = &$field[$key];
                }
            } else {
                return false;
            }
        }

        return true;
    }
}
