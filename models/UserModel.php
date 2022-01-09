<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 18:26
 */
namespace models;
use helpers\SettingsHelper;
use helpers\UrlHelper;

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
 * @property string userFolder
 * @property int score
 * @property int mulct
 * @property int old_score
 * @property bool is_admin
 * @property bool is_enabled
 * @property string guid
 * @property bool live_update
 */
class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = [ 'username', 'password', 'password_salt', 'email', 'class', 'school', 'phone', 'name', 'surname', 'score', 'mulct', 'old_score', 'is_enabled', 'guid', 'live_update' ];
    public $timestamps = true;

    public function getUserFolderAttribute()
    {
        return UrlHelper::path("users/{$this->username}");
    }

    public function generateSalt()
    {
        $this->password_salt = uniqid(mt_rand(), false);
        return $this;
    }

    public function generateGUID()
    {
        $this->guid = SettingsHelper::guid();
        return $this;
    }

    public function hashPassword($password = null)
    {
        $this->generateSalt();
        $this->password = sha1((is_null($password) ? $this->password : $password) . $this->password_salt);

        return $this;
    }

    /**
     * Get user queue
     * @param int $taks_id
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getQueue($taks_id, $limit = 0)
    {
        $query = QueueModel::select([ 'queue_id', 'user_filename', 'stan', 'tests' ])->where([
            'user_id' => $this->user_id,
            'task_id' => $taks_id
        ])->orderBy('created_at', 'desc');
        if ($limit > 0) {
            $query->take($limit);
        }

        return $query->get();
    }
}
