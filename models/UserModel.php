<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 18:26
 */
namespace models;

/**
 * Class UserModel
 * @property int user_id
 * @property string username
 * @property string password
 * @property string password_salt
 * @property string email
 * @property string class
 * @property string school
 * @property string phone
 * @property string name
 * @property string surname
 */
class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = [ 'username', 'password', 'password_salt', 'email', 'class', 'school', 'phone', 'name', 'surname' ];

    public function generateSalt()
    {
        $this->password_salt = uniqid(mt_rand(), false);
    }

    public function hashPassword()
    {
        $this->generateSalt();
        $this->password = sha1($this->password . $this->password_salt);

        return $this;
    }
}
