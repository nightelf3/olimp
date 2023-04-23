<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 06.12.2021
 * Time: 22:07
 */
namespace models;

/**
 * Class CommentModel
 * @property int comment_id
 * @property int from_id
 * @property int to_id
 * @property int task_id
 * @property string text
 * @property string created_at
 */
class CommentModel extends BaseModel
{
    protected $table = 'comments';
    protected $primaryKey = 'comment_id';
    protected $fillable = [ 'from_id', 'to_id', 'task_id', 'text' ];

    /**
     * Get user from
     *
     * @return UserModel
     */
    public function from()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'from_id');
    }

    /**
     * Get user to
     *
     * @return UserModel
     */
    public function to()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'to_id');
    }
}
