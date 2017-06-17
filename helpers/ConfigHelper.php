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
        return [
            'driver'    => 'mysql',
            'host'      => self::$config['database']['host'],
            'database'  => self::$config['database']['database'],
            'username'  => self::$config['database']['username'],
            'password'  => self::$config['database']['password'],
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => self::$config['database']['prefix']
        ];
    }

    /**
     * Get Session settings
     *
     * @return array
     */
    public static function getSessionSettings()
    {
        return isset(self::$config['sesstion']) ? self::$config['sesstion'] : [];
    }

    /**
     * Is dev environment
     *
     * @return bool
     */
    public static function isDev()
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
