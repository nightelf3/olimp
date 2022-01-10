<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 10.01.2022
 * Time: 20:37
 */
namespace models;

/**
 * Class LogModel
 * @property int comment_id
 * @property int user_id
 * @property string data
 * @property string created_at
 */
class LogModel extends BaseModel
{
    protected $table = 'logs';
    protected $primaryKey = 'log_id';
    protected $fillable = [ 'user_id', 'data' ];

    /**
     * Get user from
     *
     * @return UserModel
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'from_id');
    }
}
