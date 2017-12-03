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

    public static function isOlimpInProgress()
    {
        $olimpStart = (int)static::param('olimp_start', 0);
        $olimpEnd = $olimpStart + (int)static::param('olimp_continuity', 0);
        $currentTime = time();

        return $olimpStart <= $currentTime && $currentTime <= $olimpEnd;
    }

    public static function isOlimpStarts()
    {
        $olimpStart = (int)static::param('olimp_start', 0);
        $currentTime = time();

        return $olimpStart <= $currentTime;
    }
}
