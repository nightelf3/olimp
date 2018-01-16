<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 08.01.2018
 * Time: 16:57
 */
namespace models;
use helpers\UrlHelper;

/**
 * Class CompilationErrorModel
 * @property int queue_id
 * @property string text
 */
class CompilationErrorModel extends BaseModel
{
    protected $table = 'compilation_errors';
    protected $primaryKey = 'error_id';
    protected $fillable = [ 'queue_id', 'error' ];
}
