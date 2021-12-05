<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 21:43
 */
namespace models;

/**
 * Class CompilerModel
 * @property int compiler_id
 * @property string name
 * @property string ext
 */
class CompilerModel extends BaseModel
{
    protected $table = 'compilers';
    protected $primaryKey = 'compiler_id';
    protected $fillable = [ 'name', 'ext' ];
}
