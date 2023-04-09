<?php
/**
 * User: Night (Skype: night-elf3)
 * Date: 09.04.2023
 * Time: 16:00
 */
namespace models;

/**
 * Class CheckerModel
 * @property int checker_id
 * @property string name
 * @property int user_id
 * @property string token
 * @property bool is_active
 * @property string updated_at
 * @property string created_at
 */
class CheckerModel extends BaseModel
{
    protected $table = 'checkers';
    protected $primaryKey = 'checker_id';
    protected $fillable = [ 'name', 'user_id', 'token', 'is_active', 'updated_at' ];

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
