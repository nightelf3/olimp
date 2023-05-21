<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: night-elf3)
 * Date: 01.12.2017
 * Time: 15:19
 */
namespace models;

/**
 * Class QueueModel
 * @property int queue_id
 * @property int user_id
 * @property int task_id
 * @property string user_filename
 * @property string filename
 * @property int compiler_id
 * @property string created_at
 * @property string stan
 * @property string tests
 * @property string upload_ip
 * @property int counter
 */
class QueueModel extends BaseModel
{
    protected $table = 'queue';
    protected $primaryKey = 'queue_id';
    protected $fillable = [ 'user_id', 'task_id', 'user_filename', 'filename', 'compiler_id', 'stan', 'tests', 'upload_ip', 'counter' ];

    /**
     * Get compiler by id
     *
     * @return CompilerModel
     */
    public function compiler()
    {
        return $this->hasOne(CompilerModel::class, 'compiler_id', 'compiler_id');
    }
    /**
     * Get user by id
     *
     * @return UserModel
     */
    public function user()
    {
        return $this->hasOne(UserModel::class, 'user_id', 'user_id');
    }
}
