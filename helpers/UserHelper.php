<?php
/**
 * Created by PhpStorm.
 * User: Night (Skype: web100.vladislav.yeremeychuk)
 * Date: 17.06.2017
 * Time: 10:15
 */
namespace helpers;

use Klein\Request;
use models\LogModel;
use models\UserModel;

class UserHelper extends BaseHelper
{
    /**
     * @var UserModel|null
     */
    protected static $user = null;

    public static function initialize()
    {
        $username = SessionHelper::get('username');
        $password = SessionHelper::get('password');

        if (!is_null($username) && !is_null($password)) {
            self::$user = UserModel::where([
                'username' => $username,
                'password' => $password
            ])->first();
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
     * Is user admin and authenticated
     *
     * @return bool
     */
    public static function isAdmin()
    {
        return !is_null(self::$user) && self::$user->is_admin;
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
            ->whereRaw("password = SHA1(CONCAT('{$password}', password_salt))")->first();

        $errors = [];
        if ($user) {
            if (!$user->is_admin && SettingsHelper::param('single_login', false)) {
                $user->hashPassword($password)->update();
            }

            SessionHelper::set('username', $user->username);
            SessionHelper::set('password', $user->password);
            self::$user = $user;

            LogModel::create([
                'user_id' => self::$user->user_id,
                'data' => 'login: ' . self::getIP()
            ]);
        } else {
            $errors['userNotExists'] = true;
        }

        return $errors;
    }

    /**
     * Logout the user
     *
     * @return null
     */
    public static function logout()
    {
        if (self::$user) {
            //TODO: extend LogModel to action -> data
            LogModel::create([
                'user_id' => self::$user->user_id,
                'data' => 'logout: ' . self::getIP()
            ]);
        }
        
        SessionHelper::remove('username');
        SessionHelper::remove('password');
        self::$user = null;
    }

    /**
     * Return the user's IP
     *
     * @return string
     */
    private static function getIP()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
}
