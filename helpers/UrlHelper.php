<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 18.06.2017
 * Time: 4:12
 */
namespace helpers;

class UrlHelper extends BaseHelper
{
    /**
     * Get absolute path to file/folder
     *
     * @param string $path
     * @return null|string
     */
    public static function pathTo($path)
    {
        ErrorHelper::assert(is_string($path), "Path should be string");
        if (!is_string($path)) {
            return null;
        }

        if ($path[0] != '/') {
            $path = "/{$path}";
        }

        return BASE_PATH . $path;
    }
}