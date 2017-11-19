<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 18:14
 */
namespace models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package models
 *
 * @method static BaseModel distinct(string $dintinctField)
 * @method static BaseModel select(array $_ = [])
 * @method static BaseModel from(array $_ = [])
 * @method static BaseModel join(string $table, string $foreignKey, string $operator, string $primaryKey)
 * @method static BaseModel leftJoin(string $table, mixed $foreignKey, string $operator = null, string $primaryKey = null)
 * @method static BaseModel where(array $_ = [])
 * @method static BaseModel whereRaw(string $field, array $_ = [])
 * @method static BaseModel whereIn(array $_ = [])
 * @method static BaseModel whereNotNull(array $_ = [])
 * @method static BaseModel orWhere(array $_ = [])
 * @method static BaseModel groupBy(string $field)
 * @method static BaseModel orderBy(string $field, string $sortType = 'asc')
 * @method static BaseModel orderByRaw(string $orderString)
 * @method static BaseModel insert(array $_ = [])
 * @method static BaseModel create(array $_ = [])
 * @method static Collection take(int $limit)
 * @method static Collection get(array $_ = [])
 * @method static BaseModel first(array $_ = [])
 * @method static BaseModel find(array $_ = [])
 * @method static int count(array $_ = [])
 */
abstract class BaseModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    public function toArray($defaultArray = [])
    {
        $arr = parent::toArray();

        foreach (get_class_methods($this) as $method) {
            $match = [];
            if (preg_match("/^get([\\w]+)Attribute$/ui", $method, $match)) {
                $attrib = lcfirst($match[1]);

                $arr[$attrib] = $this->{$attrib};
            }
        }

        if ($arr) {
            return $arr;
        }
        return $defaultArray;
    }
}
