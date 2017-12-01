<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 10:15
 */
namespace helpers;

use Klein\Request;
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
            self::$user = UserModel::where([ 'user_id' => $userId ])->first();
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

    /**
     * Try to login user
     *
     * @param Request $request
     * @return array of errors
     */
    public static function login(Request $request)
    {
        $password = $request->paramsPost()->get('password', '');
        /** @var UserModel $user */
        $user = UserModel::where('username', $request->paramsPost()->get('username', ''))
            ->whereRaw("password = SHA1(CONCAT('{$password}', password_salt))")
            ->select([ 'user_id' ])->first();

        $errors = [];
        if ($user) {
            SessionHelper::set('userId', $user->user_id);
        } else {
            $errors['userNotExists'] = true;
        }

        return $errors;
    }
}
