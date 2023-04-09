<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 02.12.2017
 * Time: 10:30
 */
namespace helpers;

use Illuminate\Database\Eloquent\Collection;
use models\SettingModel;

class SettingsHelper extends BaseHelper
{
    /** @var Collection settings */
    protected static $settings = [];

    public static function initialize()
    {
        $settings = [];
        SettingModel::get()->each(function($item) use (&$settings) {
            /** @var SettingModel $item */
            $settings[$item->key] = $item->value;
        });
        self::$settings = $settings;
    }

    public static function param($key, $default = null)
    {
        return self::$settings[$key] ?: $default;
    }

    public static function setParam($key, $value)
    {
        if (isset(self::$settings[$key])) {
            SettingModel::where('key', $key)->update([ 'value' => $value ]);
        } else {
            SettingModel::create([
                'key' => $key,
                'value' => $value
            ]);
        }
        self::$settings[$key] = $value;
    }

    public static function isOlimpInProgress($orIsAdmin = true)
    {
        $olimpStart = (int)static::param('olimp_start', 0);
        $olimpEnd = $olimpStart + (int)static::param('olimp_duration', 0);
        $currentTime = time();

        return ($orIsAdmin && UserHelper::isAdmin()) || ($olimpStart <= $currentTime && $currentTime <= $olimpEnd);
    }

    public static function isOlimpStarts()
    {
        $olimpStart = (int)static::param('olimp_start', 0);
        $currentTime = time();

        return $olimpStart <= $currentTime;
    }

    public static function guid()
    {
        if (function_exists('com_create_guid'))
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
