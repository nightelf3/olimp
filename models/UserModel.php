<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 18:26
 */
namespace models;

class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = ['title'];
    protected $rules = [
        'title' => '.{1,}'
    ];
}
