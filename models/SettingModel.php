<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 02.12.2017
 * Time: 10:29
 */
namespace models;

/**
 * Class SettingModel
 * @property int setting_id
 * @property string key
 * @property string value
 */
class SettingModel extends BaseModel
{
    protected $table = 'settings';
    protected $primaryKey = 'setting_id';
    protected $fillable = [ 'key', 'value' ];
}
