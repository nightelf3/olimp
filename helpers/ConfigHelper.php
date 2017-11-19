<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 10:18
 */
namespace helpers;

class ConfigHelper extends BaseHelper
{
    protected static $config = [];

    public static function initialize()
    {
        //be careful with UrlHelper::path there
        self::$config = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(BASE_PATH . "/config/app.yml"));
    }

    /**
     * Get DB settings array
     * TODO: should we move all params to YML?
     *
     * @return array
     */
    public static function getDatabaseSettings()
    {
        ErrorHelper::assert(is_array(self::$config['database']), "Missing database settings");
        return self::$config['database'];
    }

    /**
     * Get Session settings
     *
     * @return array
     */
    public static function getSessionSettings()
    {
        return isset(self::$config['session']) ? self::$config['session'] : [];
    }

    /**
     * Is dev environment
     *
     * @return bool
     */
    public static function isDebug()
    {
        return self::$config['env'] == 'dev';
    }

    /**
     * Get field by key
     * TODO: performance?
     *
     * @param array ...$keys
     * @return string|null
     */
    public static function get(...$keys)
    {
        $filed = self::$config;

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
