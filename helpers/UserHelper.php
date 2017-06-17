<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 10:15
 */
namespace helpers;

use models\UserModel;

class UserHelper extends BaseHelper
{
    /**
     * @var UserModel|null
     */
    protected static $user = null;

    public static function initialize()
    {
        $userId = SessionHelper::get('userId');

        if (!is_null($userId)) {
            self::$user = UserModel::find([
                'user_id' => $userId
            ]);
        }
    }

    /**
     * Get user information
     *
     * @return UserModel|null
     * @throws \Exception
     */
    public static function getUser()
    {
        ErrorHelper::assert(self::isAuthenticated(), "Not authenticated!");

        return self::$user;
    }

    /**
     * Is user authenticated
     *
     * @return bool
     */
    public static function isAuthenticated()
    {
        return !is_null(self::$user);
    }
}
