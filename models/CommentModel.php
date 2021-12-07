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
 * @property int user_id
 * @property int task_id
 * @property string text
 * @property string created_at
 */
class CommentModel extends BaseModel
{
    protected $table = 'comments';
    protected $primaryKey = 'comment_id';
    protected $fillable = [ 'user_id', 'task_id', 'text' ];

    /**
     * Get user from
     *
     * @return UserModel
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'user_id');
    }
}
